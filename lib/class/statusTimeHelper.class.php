<?php

/**
 * Class statusTimeHelper
 */
final class statusTimeHelper
{
    public const MINUTES_IN_DAY    = 1440;
    public const SECONDS_IN_DAY    = 86400;
    public const MINUTES_IN_HOUR   = 60;
    public const SECONDS_IN_MINUTE = 60;
    public const MINUTES_10AM      = 600;
    public const MINUTES_18PM      = 1080;

    /**
     * @param int $minutes
     *
     * @return float
     */
    public static function getDayMinutesInPercent($minutes)
    {
        return round($minutes / (24 * 60 / 100), 1);
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
        $durationDiff = (new DateTime(date('Y-m-d H:i:s', $endTimestamp)))
            ->diff(
                new DateTime(date('Y-m-d H:i:s', $startTimestamp))
            );

        $humanFormat = [];
        $hours = 0;
        if ($durationDiff->d) {
            $hours = 24 * $durationDiff->d;
        }
        if ($durationDiff->h) {
            $hours += $durationDiff->h;
        }
        if ($hours) {
            $humanFormat[] = sprintf_wp('%dh', $hours);
        }

        if ($durationDiff->i) {
            $humanFormat[] = sprintf_wp('%dm', $durationDiff->i);
        }

        return !empty($humanFormat) ? implode(' ', $humanFormat) : $default;
    }

    /**
     * @param DateTimeInterface $date
     *
     * @return int
     */
    public static function getWeekNumberByDate(DateTimeInterface $date)
    {
        return (int) $date->format('W');
    }

    /**
     * @todo new VO for user datetime
     *
     * @param string                        $format
     * @param string|DateTimeInterface|null $time
     * @param statusUser|null               $user
     *
     * @return DateTime
     * @throws waException
     */
    public static function createDatetimeForUser($format = 'Y-m-d H:i:s', $time = null, statusUser $user = null)
    {
        if (!$user) {
            $user = stts()->getUser();
        }

        if ($time === null) {
            $time = date($format);
        } elseif ($time instanceof DateTimeInterface) {
            $time = $time->format($format);
        }

        $waTime = waDateTime::date($format, $time, $user->getTimezone());

        return new DateTime($waTime);
    }

    public static function renderIntAsHHMMTime($timeSinceMidnight = 0)
    {
        return sprintf(
            '%s:%s',
            floor($timeSinceMidnight / self::MINUTES_IN_HOUR),
            sprintf(
                '%02d',
                $timeSinceMidnight - self::SECONDS_IN_MINUTE * floor($timeSinceMidnight / self::SECONDS_IN_MINUTE)
            )
        );
    }
}
