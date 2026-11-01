<?php

namespace App\Http\Controllers;

use App\Actions\CreateMerchandiseOrder;
use App\Actions\InitiateStkPush;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\MpesaException;
use App\Http\Requests\StoreMerchandiseOrderRequest;
use App\Models\Merchandise;
use App\Services\Mpesa\DarajaClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class MerchandiseOrderController extends Controller
{
    public function store(
        StoreMerchandiseOrderRequest $request,
        Merchandise $merchandise,
        CreateMerchandiseOrder $createMerchandiseOrder,
        InitiateStkPush $initiateStkPush,
        DarajaClient $daraja,
    ): JsonResponse {
        abort_unless($merchandise->status === 'published', 404);

        if (! $daraja->isConfigured()) {
            throw new MpesaException('M-Pesa is not configured on this server.');
        }

        try {
            $order = $createMerchandiseOrder->handle($merchandise, $request->validated());
        } catch (InsufficientStockException) {
            return response()->json(
                ['message' => 'This item is out of stock.'],
                422
            );
        }

        $transaction = $initiateStkPush->handle(
            $order,
            $request->validated('mpesa_phone'),
            $order->order_reference,
            Str::limit($merchandise->name, 20, ''),
        );

        return response()->json(
            [
                'order_reference' => $order->order_reference,
                'order_id' => $order->id,
                'payment_id' => $transaction->uuid,
                'payment_status' => $transaction->status,
                'message' => 'Check your phone and enter your M-Pesa PIN to complete payment.',
            ],
            201
        );
    }
}
