<?php

namespace App\Console\Commands;

use App\Actions\CompleteMpesaPayment;
use App\Exceptions\MpesaException;
use App\Models\MpesaTransaction;
use App\Services\Mpesa\DarajaClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('mpesa:expire-pending')]
#[Description('Query or expire outstanding M-Pesa STK push payments')]
class ExpirePendingMpesaPayments extends Command
{
    public function handle(DarajaClient $daraja, CompleteMpesaPayment $completeMpesaPayment): int
    {
        $ttl = (int) config('mpesa.pending_ttl_minutes');

        $pending = MpesaTransaction::query()
            ->where('status', MpesaTransaction::STATUS_PENDING)
            ->where('created_at', '<=', now()->subMinutes(2))
            ->orderBy('id')
            ->get();

        foreach ($pending as $transaction) {
            if ($transaction->checkout_request_id) {
                try {
                    $completeMpesaPayment->fromQuery(
                        $transaction,
                        $daraja->stkQuery($transaction->checkout_request_id),
                    );
                    $transaction->refresh();
                } catch (MpesaException $exception) {
                    Log::notice('Scheduled M-Pesa query failed', [
                        'transaction_uuid' => $transaction->uuid,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }

            if ($transaction->isPending() && $transaction->created_at?->lte(now()->subMinutes($ttl))) {
                $completeMpesaPayment->fromQuery($transaction, [
                    'ResultCode' => '1037',
                    'ResultDesc' => 'Payment timed out.',
                ]);
            }
        }

        return self::SUCCESS;
    }
}
