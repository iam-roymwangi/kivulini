<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MerchandiseOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MerchandiseOrderController extends Controller
{
    /**
     * List merchandise orders with filtering by payment status and free-text search.
     */
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,paid,failed'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $orders = MerchandiseOrder::query()
            ->with(['merchandise' => fn ($q) => $q->withTrashed()->select(['id', 'name', 'slug', 'type'])])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('payment_status', $status))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('order_reference', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('contact_email', 'like', "%{$search}%")
                        ->orWhere('contact_phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all' => MerchandiseOrder::count(),
            'pending' => MerchandiseOrder::where('payment_status', 'pending')->count(),
            'paid' => MerchandiseOrder::where('payment_status', 'paid')->count(),
            'failed' => MerchandiseOrder::where('payment_status', 'failed')->count(),
        ];

        return Inertia::render('admin/orders/Index', [
            'orders' => $orders,
            'counts' => $counts,
            'filters' => [
                'status' => $filters['status'] ?? null,
                'search' => $filters['search'] ?? null,
            ],
        ]);
    }

    /**
     * Show a single order with its merchandise and M-Pesa transaction timeline.
     */
    public function show(Request $request, MerchandiseOrder $order): Response
    {
        $order->load([
            'merchandise' => fn ($q) => $q->withTrashed()->select(['id', 'name', 'slug', 'type', 'status', 'deleted_at']),
            'mpesaTransactions' => fn ($q) => $q->latest(),
        ]);

        return Inertia::render('admin/orders/Show', [
            'order' => $order,
        ]);
    }
}
