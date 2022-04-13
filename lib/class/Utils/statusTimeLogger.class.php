<?php

final class statusTimeLogger
{
    private static $prevTime = 0;
    private static $filename = '';
    private static $logs     = [];

    public static function start(string $msg, string $file = ''): void
    {
        self::$prevTime = self::getTime();
        self::$filename = $file ?: uniqid('timer_', true);

        self::$logs[] = $msg;
    }

    public static function saveTick(string $msg): void
    {
        $fromPrev = self::getTime() - self::$prevTime;
        self::$prevTime = self::getTime();

        self::$logs[] = sprintf('%s. Took %s sec: %s', self::getTime(), $fromPrev, $msg);
    }

    public static function stop(string $msg): void
    {
        self::saveTick($msg);

        foreach (self::$logs as $log) {
            stts()->getLogger()->debug($log, self::$filename);
        }

        self::$prevTime = 0;
        self::$filename = '';
        self::$logs = [];
    }

    private static function getTime(bool $micro = false)
    {
        return $micro ? microtime(true) : time();
    }
}
