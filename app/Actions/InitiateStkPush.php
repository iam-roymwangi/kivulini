<?php

namespace App\Actions;

use App\Contracts\MpesaPayable;
use App\Exceptions\MpesaException;
use App\Models\MpesaTransaction;
use App\Services\Mpesa\DarajaClient;
use App\Support\KenyanPhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InitiateStkPush
{
    public function __construct(private DarajaClient $daraja) {}

    /**
     * Validate, create a pending transaction, then fire the actual Daraja STK
     * push *after* the HTTP response is already sent to the browser.
     *
     * Laravel's defer() helper schedules the callback to run in the
     * InvokeDeferredCallbacks terminate middleware — the response is flushed
     * first, then the deferred work runs in the same PHP process. No queue
     * worker, no cron dependency, no cPanel-specific configuration required.
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

        // M-Pesa enforces a minimum transaction amount. The sandbox accepts KES 1
        // but production requires at least KES 10.
        $minAmount = app()->isProduction() ? 10 : 1;

        if ($amount < $minAmount) {
            throw new MpesaException("The payment amount (KES {$amount}) is below the M-Pesa minimum of KES {$minAmount}.");
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

        // defer() runs the STK push after the HTTP response is sent.
        // This prevents cURL timeouts from surfacing to the customer while
        // still firing the request in the same process — no queue or cron needed.
        $daraja = $this->daraja;

        defer(function () use ($transaction, $payable, $normalizedPhone, $daraja): void {
            // Re-fetch in case the transaction was already resolved during the
            // response (e.g. via a race condition or duplicate request).
            $fresh = $transaction->fresh();

            if ($fresh === null || ! $fresh->isPending() || $fresh->checkout_request_id !== null) {
                return;
            }

            $this->push($fresh, $payable, $normalizedPhone, $daraja);
        });
        // Note: push() is intentionally invoked via defer() so the STK HTTP
        // request happens after the browser response is flushed. A cURL timeout
        // inside push() means Safaricom *may* have accepted the request even
        // though we did not receive a 200 — the expire-pending cron will query
        // the transaction later and reconcile it.

        return $transaction;
    }

    /**
     * Execute the actual Daraja STK push request.
     * Called by the deferred callback — runs after the HTTP response is sent.
     *
     * On a network timeout (ConnectionException), the transaction is intentionally
     * left in "pending" status rather than marked failed. Safaricom may have
     * accepted the request before the connection dropped. The expire-pending cron
     * will query the status via stkQuery and reconcile the real outcome.
     *
     * @param  Model&MpesaPayable  $payable
     */
    public function push(MpesaTransaction $transaction, Model $payable, string $phone, ?DarajaClient $daraja = null): void
    {
        $daraja ??= $this->daraja;

        try {
            $response = $daraja->stkPush(
                $phone,
                $transaction->amount,
                $transaction->account_reference,
                $transaction->description,
                $daraja->callbackUrl(),
            );
        } catch (MpesaException $exception) {
            // A network timeout means Safaricom may still have received and
            // queued the STK prompt. Leave the transaction pending so the
            // expire-pending cron can reconcile it via stkQuery.
            if ($exception->getPrevious() instanceof ConnectionException) {
                Log::notice('M-Pesa STK push timed out — leaving transaction pending for cron reconciliation', [
                    'transaction_uuid' => $transaction->uuid,
                    'payable_type' => $transaction->payable_type,
                    'payable_id' => $transaction->payable_id,
                ]);

                return;
            }

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
