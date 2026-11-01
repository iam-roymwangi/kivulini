<?php

namespace App\Actions;

use App\Exceptions\InsufficientStockException;
use App\Models\Merchandise;
use App\Models\MerchandiseOrder;
use Illuminate\Support\Facades\DB;

class CreateMerchandiseOrder
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws InsufficientStockException
     */
    public function handle(Merchandise $merchandise, array $data): MerchandiseOrder
    {
        return DB::transaction(function () use ($merchandise, $data): MerchandiseOrder {
            /** @var Merchandise $item */
            $item = Merchandise::query()->lockForUpdate()->findOrFail($merchandise->id);

            if ($item->status !== 'published' || $item->stock < $data['quantity']) {
                throw new InsufficientStockException;
            }

            /** @var MerchandiseOrder $order */
            $order = MerchandiseOrder::create([
                'merchandise_id' => $item->id,
                'contact_name' => $data['contact_name'],
                'contact_email' => $data['contact_email'],
                'contact_phone' => $data['contact_phone'],
                'size' => $data['size'] ?? null,
                'color' => $data['color'] ?? null,
                'quantity' => $data['quantity'],
                'unit_price' => $item->price,
                'total_price' => $item->price * $data['quantity'],
                'payment_status' => 'pending',
            ]);

            $item->decrement('stock', $data['quantity']);

            return $order;
        });
    }
}
