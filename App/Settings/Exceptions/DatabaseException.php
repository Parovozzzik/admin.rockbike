<?php

namespace App\Settings\Exceptions;

/**
 * Class DatabaseException
 */
class DatabaseException extends \Exception
{
    /**
     * DatabaseException constructor.
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message = "", int $code = 500)
    {
        parent::__construct($message, $code);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}