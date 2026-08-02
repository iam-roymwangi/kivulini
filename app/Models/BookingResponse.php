<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'event_question_id',
        'answer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(EventQuestion::class, 'event_question_id');
    }
}
