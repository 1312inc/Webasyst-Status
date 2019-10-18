<?php

/**
 * Class kmwaForbiddenException
 */
class kmwaForbiddenException extends Exception
{
    /**
     * kmwaForbiddenException constructor.
     *
     * @param string $message
     * @param int    $code
     * @param null   $previous
     */
    public function __construct($message = '', $code = 500, Throwable $previous = null)
    {
        parent::__construct(_w('Access denied'), 403, $previous);
    }
}
