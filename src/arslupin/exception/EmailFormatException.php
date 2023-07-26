<?php

namespace src\arslupin\exception;

use Exception;
use Throwable;

class EmailFormatException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = 'invalid email format';
        parent::__construct($message, $code, $previous);

    }
}