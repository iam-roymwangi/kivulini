<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchandiseImage extends Model
{
    protected $fillable = [
        'merchandise_id',
        'file_path',
        'is_cover',
        'sort_order',
    ];

    protected $casts = [
        'is_cover' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function merchandise(): BelongsTo
    {
        return $this->belongsTo(Merchandise::class);
    }
}
