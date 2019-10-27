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
}
