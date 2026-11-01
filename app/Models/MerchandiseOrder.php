<?php

namespace App\Models;

use App\Contracts\MpesaPayable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MerchandiseOrder extends Model implements MpesaPayable
{
    use HasFactory;

    protected $fillable = [
        'order_reference',
        'merchandise_id',
        'contact_name',
        'contact_email',
        'contact_phone',
        'size',
        'color',
        'quantity',
        'unit_price',
        'total_price',
        'payment_status',
        'payment_reference',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MerchandiseOrder $order): void {
            if (empty($order->order_reference)) {
                $order->order_reference = 'MO-'.strtoupper(Str::random(8));
            }
        });
    }

    public function merchandise(): BelongsTo
    {
        return $this->belongsTo(Merchandise::class);
    }

    public function mpesaTransactions(): MorphMany
    {
        return $this->morphMany(MpesaTransaction::class, 'payable');
    }

    public function paymentAmountInKes(): int
    {
        return (int) round((float) $this->total_price);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function markPaymentCompleted(string $receiptNumber): void
    {
        DB::transaction(function () use ($receiptNumber): void {
            /** @var MerchandiseOrder $order */
            $order = static::query()->lockForUpdate()->findOrFail($this->id);

            if ($order->payment_status === 'paid') {
                if ($order->payment_reference === null) {
                    $order->payment_reference = $receiptNumber;
                    $order->save();
                }

                return;
            }

            $wasFailed = $order->payment_status === 'failed';

            $order->payment_status = 'paid';
            $order->payment_reference = $receiptNumber;
            $order->save();

            if ($wasFailed) {
                Merchandise::query()
                    ->whereKey($order->merchandise_id)
                    ->lockForUpdate()
                    ->decrement('stock', $order->quantity);
            }
        });
    }

    public function markPaymentFailed(): void
    {
        DB::transaction(function (): void {
            /** @var MerchandiseOrder $order */
            $order = static::query()->lockForUpdate()->findOrFail($this->id);

            if ($order->payment_status !== 'pending') {
                return;
            }

            $order->payment_status = 'failed';
            $order->save();

            Merchandise::query()
                ->whereKey($order->merchandise_id)
                ->increment('stock', $order->quantity);
        });
    }
}
