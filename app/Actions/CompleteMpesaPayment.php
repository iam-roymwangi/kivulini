<?php

namespace App\Actions;

use App\Contracts\MpesaPayable;
use App\Models\MpesaTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompleteMpesaPayment
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function fromCallback(array $payload): ?MpesaTransaction
    {
        $callback = data_get($payload, 'Body.stkCallback', []);

        if (! is_array($callback) || empty($callback['CheckoutRequestID'])) {
            return null;
        }

        $metadata = [];

        foreach (data_get($callback, 'CallbackMetadata.Item', []) as $item) {
            if (is_array($item) && isset($item['Name'])) {
                $metadata[(string) $item['Name']] = $item['Value'] ?? null;
            }
        }

        return $this->settle(
            checkoutRequestId: (string) $callback['CheckoutRequestID'],
            resultCode: (string) ($callback['ResultCode'] ?? ''),
            resultDesc: (string) ($callback['ResultDesc'] ?? ''),
            amount: isset($metadata['Amount']) ? (int) round((float) $metadata['Amount']) : null,
            receiptNumber: isset($metadata['MpesaReceiptNumber']) ? (string) $metadata['MpesaReceiptNumber'] : null,
            payload: $payload,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function fromQuery(MpesaTransaction $transaction, array $payload): ?MpesaTransaction
    {
        if (empty($transaction->checkout_request_id)) {
            return $transaction;
        }

        $resultCode = (string) ($payload['ResultCode'] ?? '');

        if ($resultCode === '') {
            return $transaction;
        }

        if ($resultCode === '4999') {
            return $transaction;
        }

        $success = $resultCode === '0';

        return $this->settle(
            checkoutRequestId: $transaction->checkout_request_id,
            resultCode: $resultCode,
            resultDesc: (string) ($payload['ResultDesc'] ?? ''),
            amount: $success ? $transaction->amount : null,
            receiptNumber: $success ? (string) ($payload['MpesaReceiptNumber'] ?? $transaction->checkout_request_id) : null,
            payload: $payload,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function settle(
        string $checkoutRequestId,
        string $resultCode,
        string $resultDesc,
        ?int $amount,
        ?string $receiptNumber,
        array $payload,
    ): ?MpesaTransaction {
        // Normalise undocumented or opaque Safaricom result descriptions to
        // something a customer (or support agent) can actually act on.
        $resultDesc = $this->humanResultDesc($resultCode, $resultDesc);

        return DB::transaction(function () use ($checkoutRequestId, $resultCode, $resultDesc, $amount, $receiptNumber, $payload): ?MpesaTransaction {
            /** @var MpesaTransaction|null $transaction */
            $transaction = MpesaTransaction::query()
                ->where('checkout_request_id', $checkoutRequestId)
                ->lockForUpdate()
                ->first();

            if ($transaction === null) {
                Log::notice('M-Pesa callback for unknown checkout request', [
                    'checkout_request_id' => $checkoutRequestId,
                ]);

                return null;
            }

            $success = $resultCode === '0';

            if ($transaction->status !== MpesaTransaction::STATUS_PENDING) {
                if ($success && is_string($receiptNumber) && $receiptNumber !== '' && $transaction->isCompleted()) {
                    $transaction->forceFill([
                        'mpesa_receipt_number' => $receiptNumber,
                        'callback_payload' => $payload,
                    ])->save();

                    $payable = $transaction->payable;

                    if ($payable instanceof MpesaPayable && $payable->isPaid()) {
                        $payable->markPaymentCompleted($receiptNumber);
                    }
                }

                return $transaction->fresh();
            }

            if ($success && ($amount === null || $amount !== $transaction->amount)) {
                Log::warning('M-Pesa callback amount mismatch', [
                    'transaction_uuid' => $transaction->uuid,
                    'expected' => $transaction->amount,
                    'received' => $amount,
                ]);

                $success = false;
                $resultDesc = 'Amount mismatch.';
            }

            if ($success && ($receiptNumber === null || $receiptNumber === '')) {
                $success = false;
                $resultDesc = 'Missing M-Pesa receipt.';
            }

            if ($success && MpesaTransaction::query()
                ->where('mpesa_receipt_number', $receiptNumber)
                ->where('id', '!=', $transaction->id)
                ->exists()
            ) {
                Log::warning('M-Pesa receipt already used', [
                    'transaction_uuid' => $transaction->uuid,
                ]);

                $success = false;
                $resultDesc = 'Duplicate M-Pesa receipt.';
            }

            $transaction->forceFill([
                'status' => $success ? MpesaTransaction::STATUS_COMPLETED : MpesaTransaction::STATUS_FAILED,
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
                'mpesa_receipt_number' => $success ? $receiptNumber : null,
                'callback_payload' => $payload,
            ])->save();

            $payable = $transaction->payable;

            if ($payable instanceof MpesaPayable) {
                if ($success) {
                    $payable->markPaymentCompleted((string) $receiptNumber);
                } else {
                    $payable->markPaymentFailed();
                }
            }

            return $transaction->fresh();
        });
    }

    /**
     * Map Safaricom result codes to human-readable descriptions.
     * Safaricom's own ResultDesc strings are often internal jargon.
     */
    private function humanResultDesc(string $resultCode, string $fallback): string
    {
        return match ($resultCode) {
            '0' => $fallback, // success — keep the receipt description
            '1' => 'The balance is insufficient to complete this payment.',
            '1001' => 'A payment is already in progress on this number. Please wait and try again.',
            '1019' => 'The M-Pesa prompt expired before a PIN was entered. Please try again.',
            '1032' => 'Payment was cancelled.',
            '1037' => 'The M-Pesa prompt could not be delivered to the phone. Please try again.',
            '2029' => 'The payment amount is below the minimum allowed by M-Pesa. Please contact support.',
            '9999' => 'M-Pesa is temporarily unavailable. Please try again shortly.',
            default => $fallback !== '' ? $fallback : 'Payment was not completed. Please try again.',
        };
    }
}
