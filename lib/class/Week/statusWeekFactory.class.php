<?php

/**
 * Class statusWeekFactory
 */
class statusWeekFactory
{
    /**
     * @param int  $n
     *
     * @param bool $includeCurrent
     *
     * @return statusWeek[]
     * @throws Exception
     */
    public static function createLastNWeeks($n = 4, $includeCurrent = false)
    {
        $weeks = [];
        $day = new DateTime();
        $i = 0;
        if ($includeCurrent) {
            $day->modify('+1 week');
        }

        while ($i++ < $n) {
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
