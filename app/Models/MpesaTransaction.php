<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class MpesaTransaction extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'uuid',
        'payable_type',
        'payable_id',
        'phone',
        'amount',
        'account_reference',
        'description',
        'merchant_request_id',
        'checkout_request_id',
        'status',
        'result_code',
        'result_desc',
        'mpesa_receipt_number',
        'callback_payload',
        'queried_at',
    ];

    protected $hidden = [
        'callback_payload',
        'merchant_request_id',
        'checkout_request_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'callback_payload' => 'array',
            'queried_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MpesaTransaction $transaction): void {
            if (empty($transaction->uuid)) {
                $transaction->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
