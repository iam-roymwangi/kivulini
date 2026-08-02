<?php

namespace App\Exceptions;

use Exception;

class BookingCapacityException extends Exception
{
    public function __construct(string $message = 'Not enough available slots to complete this booking.')
    {
        parent::__construct($message);
    }
}
