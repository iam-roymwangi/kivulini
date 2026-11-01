<?php

namespace App\Support;

final class KenyanPhoneNumber
{
    /**
     * Normalize a Kenyan mobile number to 254XXXXXXXXX for Daraja STK push.
     */
    public static function normalize(string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        $local = match (true) {
            str_starts_with($digits, '254') && strlen($digits) === 12 => substr($digits, 3),
            str_starts_with($digits, '0') && strlen($digits) === 10 => substr($digits, 1),
            strlen($digits) === 9 => $digits,
            default => null,
        };

        if ($local === null || preg_match('/^[17]\d{8}$/', $local) !== 1) {
            return null;
        }

        return '254'.$local;
    }
}
