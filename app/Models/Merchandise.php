<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Merchandise extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'merchandise';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'price',
        'sizes',
        'colors',
        'stock',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sizes' => 'array',
        'colors' => 'array',
        'stock' => 'integer',
    ];

    /**
     * @param  Builder<Merchandise>  $query
     * @return Builder<Merchandise>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function images(): HasMany
    {
        return $this->hasMany(MerchandiseImage::class)->orderBy('sort_order');
    }
}
