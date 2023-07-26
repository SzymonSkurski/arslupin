<?php

namespace src\arslupin\exception;

use Exception;
use Throwable;
class EmailMaxLengthException extends Exception
{
    public function __construct($maxLen = "", $code = 0, Throwable $previous = null)
    {
        $message = 'email max chars ' . $maxLen;
        parent::__construct($message, $code, $previous);
    }
}