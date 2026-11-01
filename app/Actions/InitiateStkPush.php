<?php

namespace App\Actions;

use App\Contracts\MpesaPayable;
use App\Exceptions\MpesaException;
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

        $transaction = MpesaTransaction::create([
            'payable_type' => $payable->getMorphClass(),
            'payable_id' => $payable->getKey(),
            'phone' => $normalizedPhone,
            'amount' => $amount,
            'account_reference' => Str::limit($accountReference, 12, ''),
            'description' => Str::limit($description, 20, ''),
            'status' => MpesaTransaction::STATUS_PENDING,
        ]);

        try {
            $response = $this->daraja->stkPush(
                $normalizedPhone,
                $amount,
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

        return $transaction->fresh();
    }

    /**
     * @param  Model&MpesaPayable  $payable
     */
    private function failTransaction(MpesaTransaction $transaction, Model $payable, string $message): void
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
