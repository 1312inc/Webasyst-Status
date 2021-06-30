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
     * @param DateTimeInterface $date
     *
     * @return statusWeek
     * @throws Exception
     */
    public static function createWeekByDate(DateTimeInterface $date)
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
    public static function getWeeksDto(array $weeks, statusGetWeekDataFilterRequestDto $filterRequestDto)
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
        if ($filterRequestDto->getProject()) {
            $user = null;
            $projectId = $filterRequestDto->getProject()->getId();
        }

        $contactIdsByDates = [];
        if ($filterRequestDto->getUsers()) {
            foreach ($filterRequestDto->getUsers() as $filterUser) {
                if ($filterUser->isDeleted()) {
                    continue;
                }
                $users[$filterUser->getContactId()] = $filterUser;
                $userDtos[$filterUser->getContactId()] = new statusUserDto($filterUser);
            }
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
                    if (!stts()->getRightConfig()->hasAccessToTeammate($contactId)) {
                        continue;
                    }

                    if (!isset($users[$contactId])) {
                        $users[$contactId] = $userRepository->findByContactId($contactId);
                        $userDtos[$contactId] = new statusUserDto($users[$contactId]);
                    }
                }
            }
        }

        // получили чекины для каждого пользователя сгрупированные по дате/контакту
        if (stts()->canShowTrace()) {
            $checkins = $checkinRepository->findWithTraceByPeriodAndContactIds($minDay, $maxDay, array_keys($users), $projectId);
        } else {
            $checkins = $checkinRepository->findByPeriodAndContactIds($minDay, $maxDay, array_keys($users), $projectId);
        }

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

        foreach ($weeks as $week) {
            $weekDto = new statusWeekDto($week);
            foreach ($week->getDays() as $day) {
                $dayDto = new statusDayDto($day);
                $weekDto->days[] = $dayDto;

                $dayDto->isFromCurrentWeek = $weekDto->isCurrent;

                // для фильтра по юзеру всегда userDto
                if (!$projectId && $users) {
                    $contactIdsByDates[$dayDto->date] = array_keys($users);
                }

                if (!isset($contactIdsByDates[$dayDto->date])) {
                    continue;
                }

                foreach ($contactIdsByDates[$dayDto->date] as $contactIdsByDate) {
                    if (!isset($userDtos[$contactIdsByDate])) {
                        continue;
                    }

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
                            $checkins[$dayDto->date][$userDto->contactId] ?? [],
                            $userDto
                        )
                        ->fillWithWalogs(
                            $userDayInfo,
                            $walogs[$userDto->contactId][$dayDto->date] ?? []
                        )
                        ->fillCheckinsWithProjects(
                            $userDayInfo->checkins,
                            $projectData[$userDto->contactId] ?? []
                        );

                    $dayDto->checkinCount += $userDayInfo->realCheckinCount;
                }
            }

            if ($projectId) {
                $weekDto->donut = $weekDtoAssembler->getDonutProjectStatDto($weekDto, $week, $projectId);
            } elseif ($users) {
                $weekDto->donut = $weekDtoAssembler->getDonutUserStatDto($weekDto, $week, $filterRequestDto->getUsers() ?: []);
            }

            $weeksDto[] = $weekDto;
        }

        return $weeksDto;
    }
}
