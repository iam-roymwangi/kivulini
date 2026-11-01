<?php

namespace App\Jobs;

use App\Actions\InitiateStkPush;
use App\Contracts\MpesaPayable;
use App\Exceptions\MpesaException;
use App\Models\MpesaTransaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;

class SendStkPush implements ShouldQueue
{
    use Queueable;

    /**
     * Number of times the job may be attempted.
     * The STK push is idempotent per transaction — if the Daraja call
     * succeeds on a retry the CheckoutRequestID is stored as normal.
     */
    public int $tries = 3;

    /**
     * Seconds to wait before retrying after a connection failure.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30];

    /**
     * @param  Model&MpesaPayable  $payable
     */
    public function __construct(
        public readonly Model $payable,
        public readonly MpesaTransaction $transaction,
        public readonly string $phone,
    ) {}

    public function handle(InitiateStkPush $action): void
    {
        // Skip if the transaction was already resolved (e.g. by a previous attempt).
        if (! $this->transaction->isPending()) {
            return;
        }

        // Skip if checkout already initiated (job ran twice).
        if ($this->transaction->checkout_request_id !== null) {
            return;
        }

        try {
            $action->push($this->transaction, $this->payable, $this->phone);
        } catch (MpesaException $exception) {
            // MpesaException means Daraja rejected or is misconfigured.
            // Don't retry — the transaction is already marked failed by failTransaction().
            $this->fail($exception);
        }
    }
}
