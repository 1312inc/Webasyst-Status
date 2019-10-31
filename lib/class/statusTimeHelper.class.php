<?php

/**
 * Class statusTimeHelper
 */
final class statusTimeHelper
{
    /**
     * @param int $minutes
     *
     * @return float
     */
    public static function getDayMinutesInPercent($minutes)
    {
        return round($minutes / (24 * 60 / 100));
    }
}
