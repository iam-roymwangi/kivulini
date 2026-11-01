<?php

namespace App\Actions;

use App\Contracts\MpesaPayable;
use App\Exceptions\MpesaException;
use App\Jobs\SendStkPush;
use App\Models\MpesaTransaction;
use App\Services\Mpesa\DarajaClient;
use App\Support\KenyanPhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InitiateStkPush
{
    public function __construct(private DarajaClient $daraja) {}

    /**
     * Validate, create a pending transaction, and dispatch the STK push as a
     * queued job so that cPanel's slow / throttled outbound connections don't
     * hold up the HTTP response.
     *
     * The frontend already polls PaymentStatusController by payment_id, so it
     * will pick up the CheckoutRequestID once the job fires and the callback
     * arrives — no change needed on the frontend side.
     *
     * @param  Model&MpesaPayable  $payable
     */
    public function handle(Model $payable, string $phone, string $accountReference, string $description): MpesaTransaction
    {
        if (! $payable instanceof MpesaPayable) {
            throw new MpesaException('This item cannot be paid with M-Pesa.');
        }

        if (! $this->daraja->isConfigured()) {
            throw new MpesaException('M-Pesa is not configured on this server.');
        }

        $normalizedPhone = KenyanPhoneNumber::normalize($phone);

        if ($normalizedPhone === null) {
            throw new MpesaException('Enter a valid Kenyan M-Pesa number.');
        }

        $amount = $payable->paymentAmountInKes();

        if ($amount < 1) {
            throw new MpesaException('This order has no payable amount.');
        }

        // M-Pesa production enforces a minimum of KES 10 per transaction.
        // Sending less results in result code 2029 ("Failed due to an unresolved
        // reason type") — Safaricom's undocumented floor-amount rejection.
        if ($amount < 10) {
            throw new MpesaException("The minimum M-Pesa payment is KES 10 (order total: KES {$amount}).");
        }

        $transaction = MpesaTransaction::create([
            'payable_type' => $payable->getMorphClass(),
            'payable_id' => $payable->getKey(),
            'phone' => $normalizedPhone,
            'amount' => $amount,
            'account_reference' => Str::limit($accountReference, 12, ''),
            'description' => Str::limit($description, 20, ''),
            'status' => MpesaTransaction::STATUS_PENDING,
        ]);

        // Dispatch asynchronously so the HTTP response is not held open while
        // waiting for the Daraja connection (cURL error 28 on cPanel).
        SendStkPush::dispatch($payable, $transaction, $normalizedPhone);

        return $transaction;
    }

    /**
     * Execute the actual Daraja STK push request.
     * Called by SendStkPush job — not called directly from the HTTP layer.
     *
     * @param  Model&MpesaPayable  $payable
     */
    public function push(MpesaTransaction $transaction, Model $payable, string $phone): void
    {
        try {
            $response = $this->daraja->stkPush(
                $phone,
                $transaction->amount,
                $transaction->account_reference,
                $transaction->description,
                $this->daraja->callbackUrl(),
            );
        } catch (MpesaException $exception) {
            $this->failTransaction($transaction, $payable, $exception->getMessage());

            throw $exception;
        }

        $checkoutRequestId = $response['CheckoutRequestID'] ?? null;

        if (! is_string($checkoutRequestId) || $checkoutRequestId === '') {
            $this->failTransaction($transaction, $payable, 'M-Pesa did not return a checkout request.');

            throw new MpesaException('M-Pesa did not return a checkout request.');
        }

        $transaction->forceFill([
            'merchant_request_id' => $response['MerchantRequestID'] ?? null,
            'checkout_request_id' => $checkoutRequestId,
        ])->save();
    }

    /**
     * @param  Model&MpesaPayable  $payable
     */
    public function failTransaction(MpesaTransaction $transaction, Model $payable, string $message): void
    {
        $transaction->forceFill([
            'status' => MpesaTransaction::STATUS_FAILED,
            'result_desc' => $message,
        ])->save();

        $payable->markPaymentFailed();

        Log::notice('M-Pesa STK push failed', [
            'transaction_uuid' => $transaction->uuid,
            'payable_type' => $transaction->payable_type,
            'payable_id' => $transaction->payable_id,
        ]);
    }
}
