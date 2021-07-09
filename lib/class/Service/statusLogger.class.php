<?php

/**
 * Class statusLogger
 */
final class statusLogger
{
    /**
     * @param        $message
     * @param string $file
     */
    public function debug($message, $file = 'debug')
    {
        if (waSystemConfig::isDebug()) {
            if (is_string($message)) {
                $this->log($message, $file);
            } else {
                $this->log($message, $file);
            }
        }
    }

    /**
     * @param        $message
     * @param string $file
     */
    public function log($message, $file = 'log')
    {
        if (is_string($message)) {
            waLog::log($message, sprintf('status/%s.log', $file));
        } else {
            waLog::dump($message, sprintf('status/%s.log', $file));
        }
    }

    /**
     * @param string         $message
     * @param Exception|null $exception
     * @param string         $file
     */
    public function error($message, Exception $exception = null, $file = 'error')
    {
        $this->log($message, $file);
        if ($exception instanceof Exception) {
            if ($message !== $exception->getMessage()) {
                $this->log($exception->getMessage(), $file);
            }
            $this->log($exception->getTraceAsString(), $file);
        }
    }
}
