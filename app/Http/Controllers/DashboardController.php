<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Render the admin/user dashboard with system metrics.
     */
    public function __invoke(Request $request): Response
    {
        $totalRevenue = Booking::where('payment_status', 'paid')->sum('total_price');
        $totalBookings = Booking::count();
        $ticketsSold = Booking::sum('quantity') ?? 0;
        $activeEventsCount = Event::published()->count();
        $totalEventsCount = Event::count();

        $recentBookings = Booking::with('event')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function (Booking $booking) {
                return [
                    'id' => $booking->id,
                    'reference' => $booking->booking_reference,
                    'event_title' => $booking->event?->title ?? 'Unknown Event',
                    'contact_name' => $booking->contact_name,
                    'contact_email' => $booking->contact_email,
                    'quantity' => $booking->quantity,
                    'total_price' => $booking->total_price,
                    'payment_status' => $booking->payment_status,
                    'created_at' => $booking->created_at?->toIso8601String(),
                ];
            });

        return Inertia::render('Dashboard', [
            'metrics' => [
                'total_revenue' => (float) $totalRevenue,
                'total_bookings' => $totalBookings,
                'tickets_sold' => (int) $ticketsSold,
                'active_events' => $activeEventsCount,
                'total_events' => $totalEventsCount,
            ],
            'recent_bookings' => $recentBookings,
        ]);
    }
}
