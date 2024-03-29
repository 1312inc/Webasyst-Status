<?php

/**
 * Class kmwaNotFoundException
 */
class kmwaNotFoundException extends Exception
{
    /**
     * kmwaNotFoundException constructor.
     *
     * @param string $message
     * @param int    $code
     * @param null   $previous
     */
    public function __construct($message = '', $code = 500, $previous = null)
    {
        parent::__construct(_w('Not found'), 404, $previous);
    }
}
