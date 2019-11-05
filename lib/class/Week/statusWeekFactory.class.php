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
        return new statusWeek(new DateTime());
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
        /** @var statusWeek $week */
        foreach ($weeks as $week) {
            $weeksDto[] = new statusWeekDto($week, $checkins);
        }

        return $weeksDto;
    }
}
