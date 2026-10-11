<?php

namespace App\Http\Resources;

use App\Models\Merchandise;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MerchandiseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Merchandise $this */
        $coverImage = $this->whenLoaded('images', function () {
            return $this->images->firstWhere('is_cover', true) ?? $this->images->first();
        });

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'description' => $this->description,
            'price' => $this->price,
            'sizes' => $this->sizes ?? [],
            'colors' => $this->colors ?? [],
            'stock' => $this->stock,
            'status' => $this->status,
            'cover_image_url' => $coverImage
                ? Storage::url($coverImage->file_path)
                : null,
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => Storage::url($img->file_path),
                    'file_path' => $img->file_path,
                    'is_cover' => $img->is_cover,
                    'sort_order' => $img->sort_order,
                ])->values()->all();
            }, []),
        ];
    }
}
