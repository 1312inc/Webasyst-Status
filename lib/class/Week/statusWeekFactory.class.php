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
     * @return statusWeek
     * @throws Exception
     */
    public static function createWeekByDate(DateTime $date)
    {
        return new statusWeek($date);
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
    public static function getWeeksDto(statusUser $user, $n = self::DEFAULT_WEEKS_LOAD, $withCurrent = false, $offset = 0)
    {
        $weeks = self::createLastNWeeks($n, $withCurrent, self::DEFAULT_WEEKS_LOAD * $offset);
        $weeksDto = [];

        /** @var statusCheckinRepository $checkinRepository */
        $checkinRepository = stts()->getEntityRepository(statusCheckin::class);

        $checkins = $checkinRepository->findByWeeks($weeks, $user);
        $walogs = (new statusWaLogParser())->parseByWeeks($weeks, $user);

        /** @var statusWeek $week */
        foreach ($weeks as $week) {
            $weekDto = new statusWeekDto($week);
            foreach ($week->getDays() as $day) {
                $date = $day->getDate()->format('Y-m-d');
                $dayDto = new statusDayDto(
                    $day,
                    isset($checkins[$date]) ? $checkins[$date] : []
                );
                $dayDto->isFromCurrentWeek = $weekDto->isCurrent;
                $weekDto->days[] = $dayDto;

                if (isset($walogs[$date])) {
                    foreach ($walogs[$date] as $appId => $walog) {
                        $dayDto->walogs[$appId] = new statusWaLogDto($appId, $walog);
                    }
                }
            }

            $weeksDto[] = $weekDto;
        }

        return $weeksDto;
    }
}
