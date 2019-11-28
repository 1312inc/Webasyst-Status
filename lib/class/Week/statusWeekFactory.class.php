<?php

/**
 * Class statusWeekFactory
 */
class statusWeekFactory
{
    const DEFAULT_WEEKS_LOAD = 4;

    /**
     * @param int  $n
     * @param bool $includeCurrent
     * @param int  $from
     *
     * @return statusWeek[]
     * @throws Exception
     */
    public static function createLastNWeeks($n = self::DEFAULT_WEEKS_LOAD, $includeCurrent = false, $from = 0)
    {
        $weeks = [];
        $day = new DateTime();
        if ($includeCurrent) {
            $day->modify('+1 week');
        }
        $to = $from + $n;
        if ($from) {
            $day->modify("-{$from} week");
        }

        while ($from++ < $to) {
            $weeks[] = new statusWeek($day->modify('-1 week'));
        }

        return $weeks;
    }

    /**
     * @return statusWeek
     * @throws Exception
     */
    public static function createCurrentWeek()
    {
        return self::createWeekByDate(new DateTime());
    }

    /**
     * @param DateTime $date
     *
     * @return statusWeek
     * @throws Exception
     */
    public static function createWeekByDate(DateTime $date)
    {
        return new statusWeek($date);
    }

    /**
     * @param int $num
     *
     * @return statusWeek
     * @throws Exception
     */
    public static function createWeekByNum($num)
    {
        $date = new DateTime();
        $date->setISODate($date->format('Y'), $num, 1);
        $week = self::createWeekByDate($date);

        return $week;
    }

    /**
     * @param statusWeek[]       $weeks
     * @param statusUser|null    $user
     * @param statusProject|null $project
     *
     * @return statusWeekDto[]
     * @throws waException
     */
    public static function getWeeksDto(array $weeks, statusUser $user = null, statusProject $project = null)
    {
        $weeksDto = [];
        $projectId = false;

        if (empty($weeks)) {
            return $weeksDto;
        }

        /** @var statusUserRepository $userRepository */
        $userRepository = stts()->getEntityRepository(statusUser::class);
        /** @var statusCheckinRepository $checkinRepository */
        $checkinRepository = stts()->getEntityRepository(statusCheckin::class);

        $maxDay = $weeks[0]->getLastDay();
        $minDay = $weeks[count($weeks) - 1]->getFirstDay();

        // собрали пользователей для заданных недель
        $users = [];
        $userDtos = [];
        if ($project instanceof statusProject) {
            $user = null;
            $projectId = $project->getId();
        }

        $contactIdsByDates = [];
        if ($user instanceof statusUser) {
            $users[$user->getContactId()] = $user;
            $userDtos[$user->getContactId()] = new statusUserDto($users[$user->getContactId()]);
        } else {
            /** @var statusCheckinModel $checkinModel */
            $checkinModel = stts()->getModel(statusCheckin::class);
            $contactIdsByDates = $checkinModel->getContactIdsGroupedByDays(
                $minDay->getDate()->format('Y-m-d'),
                $maxDay->getDate()->format('Y-m-d'),
                $projectId
            );

            foreach ($contactIdsByDates as $date => $contactIds) {
                foreach ($contactIds as $contactId) {
                    if (!isset($users[$contactId])) {
                        $users[$contactId] = $userRepository->findByContactId($contactId);
                        $userDtos[$contactId] = new statusUserDto($users[$contactId]);
                    }
                }
            }
        }

        // получили чекины для каждого пользователя сгрупированные по дате/контакту
        $checkins = $checkinRepository->findByPeriodAndContactIds($minDay, $maxDay, array_keys($users), $projectId);

        /** @var statusProjectModel $projectModel */
        $projectModel = stts()->getModel(statusProject::class);
        $logParser = new statusWaLogParser();
        $walogs = [];
        $projectData = [];
        foreach ($userDtos as $userDto) {
            $walogs[$userDto->contactId] = $logParser->parseByDays($minDay, $maxDay, $userDto->contactId);
            $projectData[$userDto->contactId] = $projectModel->getByDatesAndContactId(
                $minDay->getDate()->format('Y-m-d'),
                $maxDay->getDate()->format('Y-m-d'),
                $userDto->contactId
            );
        }

        $dayDtoAssembler = new statusDayDotAssembler();
        $weekDtoAssembler = new statusWeekDtoAssembler();

        /** @var statusWeek $week */
        foreach ($weeks as $week) {
            $weekDto = new statusWeekDto($week);
            foreach ($week->getDays() as $day) {
                $dayDto = new statusDayDto($day);
                $weekDto->days[] = $dayDto;

                $dayDto->isFromCurrentWeek = $weekDto->isCurrent;

                // для фильтра по юзеру всегда userDto
                if (!$projectId) {
                    $contactIdsByDates[$dayDto->date] = [$user->getContactId()];
                }

                if (!isset($contactIdsByDates[$dayDto->date])) {
                    continue;
                }

                foreach ($contactIdsByDates[$dayDto->date] as $contactIdsByDate) {
                    $userDto = $userDtos[$contactIdsByDate];

                    $dayDto->users[$userDto->contactId] = $userDto;

                    // + инфа о дне пользователя
                    $userDayInfo = new statusDayUserInfoDto($dayDto->date, $userDto->contactId);
                    $dayDto->userDayInfos[$userDto->contactId] = $userDayInfo;

                    $userDayInfo->todayStatus = statusTodayStatusFactory::getForContactId(
                        $userDto->contactId,
                        new DateTime($dayDto->date)
                    );

                    $dayDtoAssembler
                        ->fillWithCheckins(
                            $userDayInfo,
                            isset($checkins[$dayDto->date][$userDto->contactId]) ? $checkins[$dayDto->date][$userDto->contactId] : [],
                            $userDto
                        )
                        ->fillWithWalogs(
                            $userDayInfo,
                            isset($walogs[$userDto->contactId][$dayDto->date]) ? $walogs[$userDto->contactId][$dayDto->date] : []
                        )
                        ->fillCheckinsWithProjects($userDayInfo->checkins, $projectData);
                }
            }

            if (!$projectId) {
                $weekDto->donut = $weekDtoAssembler->getDonutUserStatDto($weekDto, $week, $userDto);
            } else {
                $weekDto->donut = $weekDtoAssembler->getDonutProjectStatDto($weekDto, $week, $projectId);
                $weekDto->donut->chart = false;
            }

            $weeksDto[] = $weekDto;
        }

        return $weeksDto;
    }
}
