<?php

namespace App\Actions;

use App\Models\Event;

class SyncEventPriceTiers
{
    /**
     * Replace the event's price tiers with the submitted list.
     *
     * Tiers missing from the list are deleted; existing bookings keep their
     * tier name and unit price snapshot.
     *
     * @param  array<int, array{id?: int|string|null, name: string, price: int|float|string, description?: string|null}>  $tiers
     */
    public function handle(Event $event, array $tiers): void
    {
        // Validated arrays can come back keyed out of order; keys reflect the submitted order.
        ksort($tiers);

        $keptIds = collect($tiers)->pluck('id')->filter()->map(fn ($id): int => (int) $id)->all();

        $event->priceTiers()->whereNotIn('id', $keptIds)->delete();

        foreach (array_values($tiers) as $index => $tier) {
            $attributes = [
                'name' => trim($tier['name']),
                'price' => $tier['price'],
                'description' => filled($tier['description'] ?? null) ? trim((string) $tier['description']) : null,
                'sort_order' => $index,
            ];

            $existing = filled($tier['id'] ?? null)
                ? $event->priceTiers()->whereKey((int) $tier['id'])->first()
                : null;

            $existing !== null
                ? $existing->update($attributes)
                : $event->priceTiers()->create($attributes);
        }
    }
}
