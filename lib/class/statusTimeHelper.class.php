<?php

/**
 * Class statusTimeHelper
 */
final class statusTimeHelper
{
    const MINUTES_IN_DAY = 1440;
    const MINUTES_IN_HOUR = 60;
    const SECONDS_IN_MINUTE = 60;

    /**
     * @param int $minutes
     *
     * @return float
     */
    public static function getDayMinutesInPercent($minutes)
    {
        return round($minutes / (24 * 60 / 100));
    }

    /**
     * @param int    $startTimestamp
     * @param int    $endTimestamp
     * @param string $default
     *
     * @return string
     * @throws Exception
     */
    public static function getTimeDurationInHuman($startTimestamp, $endTimestamp, $default = '0')
    {
        $durationDiff = (new DateTime(date('Y-m-d H:i:s', $endTimestamp)))->diff(new DateTime(date('Y-m-d H:i:s', $startTimestamp)));

        $humanFormat = [];
        if ($durationDiff->h) {
            $humanFormat[] = sprintf_wp('%dh', $durationDiff->h);
        }

        if ($durationDiff->i) {
            $humanFormat[] = sprintf_wp('%dm', $durationDiff->i);
        }

        return !empty($humanFormat) ? implode(' ', $humanFormat) : $default;
    }

    /**
     * @param DateTime $date
     *
     * @return int
     */
    public static function getWeekNumberByDate(DateTime $date)
    {
        return (int)$date->format('W');
    }
}
