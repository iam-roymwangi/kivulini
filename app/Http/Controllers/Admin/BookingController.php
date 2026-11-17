<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBookingFollowUpRequest;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    /**
     * List event bookings with payment, follow-up, event and free-text filters.
     */
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,paid,failed,cancelled'],
            'follow_up' => ['nullable', Rule::in(Booking::FOLLOW_UP_STATUSES)],
            'event' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $bookings = Booking::query()
            ->with(['event' => fn ($query) => $query->withTrashed()->select(['id', 'title', 'slug', 'start_date'])])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('payment_status', $status))
            ->when($filters['follow_up'] ?? null, fn (Builder $query, string $followUp) => $query->where('follow_up_status', $followUp))
            ->when($filters['event'] ?? null, fn (Builder $query, int|string $eventId) => $query->where('event_id', $eventId))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('booking_reference', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('contact_email', 'like', "%{$search}%")
                        ->orWhere('contact_phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $statusCounts = Booking::query()
            ->when($filters['event'] ?? null, fn (Builder $query, int|string $eventId) => $query->where('event_id', $eventId))
            ->selectRaw('payment_status, count(*) as total')
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        return Inertia::render('admin/bookings/Index', [
            'bookings' => $bookings,
            'counts' => [
                'all' => (int) $statusCounts->sum(),
                'pending' => (int) ($statusCounts['pending'] ?? 0),
                'paid' => (int) ($statusCounts['paid'] ?? 0),
                'failed' => (int) ($statusCounts['failed'] ?? 0),
                'cancelled' => (int) ($statusCounts['cancelled'] ?? 0),
            ],
            'events' => Event::withTrashed()->orderByDesc('start_date')->get(['id', 'title', 'start_date']),
            'followUpStatuses' => Booking::FOLLOW_UP_STATUSES,
            'filters' => [
                'status' => $filters['status'] ?? null,
                'follow_up' => $filters['follow_up'] ?? null,
                'event' => isset($filters['event']) ? (int) $filters['event'] : null,
                'search' => $filters['search'] ?? null,
            ],
        ]);
    }

    /**
     * Show a booking with its answers, consent, payments and follow-up notes.
     */
    public function show(Booking $booking): Response
    {
        $booking->load([
            'event' => fn ($query) => $query->withTrashed()->select(['id', 'title', 'slug', 'start_date', 'end_date', 'location', 'pickup_location', 'deleted_at']),
            'responses.question:id,question_text',
            'consent',
            'mpesaTransactions' => fn ($query) => $query->latest(),
        ]);

        return Inertia::render('admin/bookings/Show', [
            'booking' => $booking,
            'followUpStatuses' => Booking::FOLLOW_UP_STATUSES,
        ]);
    }

    /**
     * Record the outcome of an admin follow-up with the customer.
     */
    public function updateFollowUp(UpdateBookingFollowUpRequest $request, Booking $booking): RedirectResponse
    {
        $booking->update([
            ...$request->validated(),
            'followed_up_at' => $request->validated('follow_up_status') === 'new' ? null : now(),
        ]);

        return back()->with('success', 'Follow-up saved.');
    }
}
