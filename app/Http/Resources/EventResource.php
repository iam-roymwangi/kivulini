<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Event $this */
        $featuredMedia = $this->whenLoaded('media', function () {
            return $this->media->firstWhere('is_featured', true);
        });

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type,
            'summary' => $this->summary,
            'description' => $this->description,
            'location' => $this->location,
            'pickup_location' => $this->pickup_location,
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'price' => $this->price,
            'capacity' => $this->capacity,
            'booked_slots' => $this->booked_slots,
            'available_slots' => $this->available_slots,
            'status' => $this->status,
            'liability_waiver_text' => $this->liability_waiver_text,
            'cover_image_url' => $featuredMedia
                ? \Illuminate\Support\Facades\Storage::url($featuredMedia->file_path)
                : null,
            'media' => EventMediaResource::collection($this->whenLoaded('media')),
            'questions' => EventQuestionResource::collection($this->whenLoaded('questions')),
        ];
    }
}
