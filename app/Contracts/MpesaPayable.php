<?php

namespace App\Contracts;

interface MpesaPayable
{
    public function paymentAmountInKes(): int;

    public function markPaymentCompleted(string $receiptNumber): void;

    public function markPaymentFailed(): void;

    public function isPaid(): bool;
}
