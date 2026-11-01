<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\MpesaTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MpesaTransaction>
 */
class MpesaTransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'payable_type' => Booking::class,
            'payable_id' => Booking::factory(),
            'phone' => '254700000001',
            'amount' => 1500,
            'account_reference' => 'BK-TEST0001',
            'description' => 'Event booking',
            'merchant_request_id' => 'mr-'.Str::random(8),
            'checkout_request_id' => 'ws_CO_'.Str::random(12),
            'status' => MpesaTransaction::STATUS_PENDING,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => MpesaTransaction::STATUS_COMPLETED,
            'result_code' => '0',
            'mpesa_receipt_number' => 'NLJ7RT61SV',
        ]);
    }
}
