<?php

namespace src\arslupin\exception;

use Exception;
use Throwable;

class BadTokenException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if ($message === '') {
            $message = 'bad_token';
        }
        parent::__construct($message, $code, $previous);
    }
}