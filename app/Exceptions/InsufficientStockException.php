<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(string $message = 'Not enough stock to complete this order.')
    {
        parent::__construct($message);
    }
}
