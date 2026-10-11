<?php

namespace App\Http\Resources;

use App\Models\MerchandiseImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MerchandiseImageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var MerchandiseImage $this */
        return [
            'id' => $this->id,
            'url' => Storage::url($this->file_path),
            'file_path' => $this->file_path,
            'is_cover' => $this->is_cover,
            'sort_order' => $this->sort_order,
        ];
    }
}
