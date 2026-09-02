<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'type',
        'summary',
        'description',
        'location',
        'pickup_location',
        'start_date',
        'end_date',
        'price',
        'capacity',
        'booked_slots',
        'status',
        'liability_waiver_text',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'price' => 'decimal:2',
        'capacity' => 'integer',
        'booked_slots' => 'integer',
    ];

    /**
     * Scopes
     */

    /**
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Relationships
     */
    public function media(): HasMany
    {
        return $this->hasMany(EventMedia::class)->orderBy('sort_order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(EventQuestion::class)->orderBy('sort_order');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Helpers
     */
    public function getAvailableSlotsAttribute(): int
    {
        return max(0, $this->capacity - $this->booked_slots);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->booked_slots >= $this->capacity;
    }
}
