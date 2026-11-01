<?php

namespace App\Http\Controllers;

use App\Actions\CompleteMpesaPayment;
use App\Exceptions\MpesaException;
use App\Models\Booking;
use App\Models\MerchandiseOrder;
use App\Models\MpesaTransaction;
use App\Services\Mpesa\DarajaClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentStatusController extends Controller
{
    public function __invoke(
        MpesaTransaction $mpesaTransaction,
        DarajaClient $daraja,
        CompleteMpesaPayment $completeMpesaPayment,
    ): JsonResponse {
        if ($mpesaTransaction->isPending() && $this->shouldQueryDaraja($mpesaTransaction)) {
            $this->queryDaraja($mpesaTransaction, $daraja, $completeMpesaPayment);
            $mpesaTransaction->refresh();
        }

        $payable = $mpesaTransaction->payable;

        $paymentStatus = null;
        $reference = null;

        if ($payable instanceof Booking) {
            $paymentStatus = $payable->payment_status;
            $reference = $payable->booking_reference;
        }

        if ($payable instanceof MerchandiseOrder) {
            $paymentStatus = $payable->payment_status;
            $reference = $payable->order_reference;
        }

        return response()->json([
            'payment_id' => $mpesaTransaction->uuid,
            'status' => $mpesaTransaction->status,
            'result_desc' => $mpesaTransaction->isPending() ? null : $mpesaTransaction->result_desc,
            'payable' => [
                'type' => class_basename($mpesaTransaction->payable_type),
                'payment_status' => $paymentStatus,
                'reference' => $reference,
            ],
        ]);
    }

    private function shouldQueryDaraja(MpesaTransaction $transaction): bool
    {
        if ($transaction->checkout_request_id === null) {
            return false;
        }

        if ($transaction->created_at?->gt(now()->subSeconds((int) config('mpesa.query_after_seconds'))) ?? true) {
            return false;
        }

        return $transaction->queried_at === null
            || $transaction->queried_at->lte(now()->subSeconds(20));
    }

    private function queryDaraja(
        MpesaTransaction $transaction,
        DarajaClient $daraja,
        CompleteMpesaPayment $completeMpesaPayment,
    ): void {
        $lock = Cache::lock('mpesa-query-'.$transaction->uuid, 15);

        if (! $lock->get()) {
            return;
        }

        try {
            $transaction->forceFill(['queried_at' => now()])->save();
            $completeMpesaPayment->fromQuery($transaction, $daraja->stkQuery($transaction->checkout_request_id));
        } catch (MpesaException $exception) {
            Log::notice('M-Pesa status query failed', [
                'transaction_uuid' => $transaction->uuid,
                'message' => $exception->getMessage(),
            ]);
        } finally {
            $lock->release();
        }
    }
}
