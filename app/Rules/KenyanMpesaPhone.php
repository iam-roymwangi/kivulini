<?php

namespace App\Rules;

use App\Support\KenyanPhoneNumber;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class KenyanMpesaPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || KenyanPhoneNumber::normalize($value) === null) {
            $fail('Enter a valid Kenyan M-Pesa number, for example 07xx xxx xxx.');
        }
    }
}
