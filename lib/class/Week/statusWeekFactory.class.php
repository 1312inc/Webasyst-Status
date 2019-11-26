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
     * @param statusUser $user
     * @param int        $n
     * @param bool       $withCurrent
     * @param int        $offset
     *
     * @return statusWeekDto[]
     * @throws waException
     * @throws Exception
     */
    public static function getWeeksDto(
        statusUser $user,
        $n = self::DEFAULT_WEEKS_LOAD,
        $withCurrent = false,
        $offset = 0
    ) {
        $weeks = self::createLastNWeeks($n, $withCurrent, self::DEFAULT_WEEKS_LOAD * $offset);
        $weeksDto = [];

        if (empty($weeks)) {
            return $weeksDto;
        }

        /** @var statusCheckinRepository $checkinRepository */
        $checkinRepository = stts()->getEntityRepository(statusCheckin::class);

        $checkins = $checkinRepository->findByWeeks($weeks, $user);

        $maxDay = $weeks[0]->getLastDay();
        $minDay = $weeks[count($weeks) - 1]->getFirstDay();

        $walogs = (new statusWaLogParser())->parseByDays($minDay, $maxDay, $user);

        /** @var statusProjectModel $projectModel */
        $projectModel = stts()->getModel(statusProject::class);
        $projectData = $projectModel->getByDatesAndContactId(
            $minDay->getDate()->format('Y-m-d'),
            $maxDay->getDate()->format('Y-m-d'),
            $user->getContactId()
        );

        $dayDtoAssembler = new statusDayDotAssembler();
        $weekDtoAssembler = new statusWeekDtoAssembler();

        /** @var statusWeek $week */
        foreach ($weeks as $week) {
            $weekDto = new statusWeekDto($week);
            foreach ($week->getDays() as $day) {
                $dayDto = new statusDayDto($day);
                $dayDto->isFromCurrentWeek = $weekDto->isCurrent;

                $dayDtoAssembler
                    ->fillWithCheckins($dayDto, isset($checkins[$dayDto->date]) ? $checkins[$dayDto->date] : [], $user)
                    ->fillWithWalogs($dayDto, isset($walogs[$dayDto->date]) ? $walogs[$dayDto->date] : [])
                    ->fillCheckinsWithProjects($dayDto->checkins, $projectData)
                ;

                $weekDto->days[] = $dayDto;
            }

            $weekDto->donut = $weekDtoAssembler->getDonutUserStatDto($weekDto, $week, $user);

            $weeksDto[] = $weekDto;
        }

        return $weeksDto;
    }
}
