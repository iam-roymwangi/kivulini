<?php

namespace App\Models;

use App\Contracts\MpesaPayable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Booking extends Model implements MpesaPayable
{
    use HasFactory;

    protected $fillable = [
        'booking_reference',
        'event_id',
        'user_id',
        'contact_name',
        'contact_email',
        'contact_phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'quantity',
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
            'total_price' => 'decimal:2',
        ];
    }

    /**
     * Auto-generate reference on creation
     */
    protected static function booted(): void
    {
        static::creating(function ($booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = 'BK-'.strtoupper(Str::random(8));
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(BookingResponse::class);
    }

    public function consent(): HasOne
    {
        return $this->hasOne(Consent::class);
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
            /** @var Booking $booking */
            $booking = static::query()->lockForUpdate()->findOrFail($this->id);

            if ($booking->payment_status === 'paid') {
                if ($booking->payment_reference === null || $booking->payment_reference !== $receiptNumber) {
                    $booking->payment_reference = $receiptNumber;
                    $booking->save();
                }

                return;
            }

            $wasFailed = $booking->payment_status === 'failed';

            $booking->payment_status = 'paid';
            $booking->payment_reference = $receiptNumber;
            $booking->save();

            if ($wasFailed) {
                Event::query()
                    ->whereKey($booking->event_id)
                    ->lockForUpdate()
                    ->increment('booked_slots', $booking->quantity);
            }
        });
    }

    public function markPaymentFailed(): void
    {
        DB::transaction(function (): void {
            /** @var Booking $booking */
            $booking = static::query()->lockForUpdate()->findOrFail($this->id);

            if ($booking->payment_status !== 'pending') {
                return;
            }

            $booking->payment_status = 'failed';
            $booking->save();

            Event::query()
                ->where('id', $booking->event_id)
                ->where('booked_slots', '>=', $booking->quantity)
                ->decrement('booked_slots', $booking->quantity);
        });
    }
}
