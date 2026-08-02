<?php

namespace App\Http\Controllers;

use App\Actions\CreateBooking;
use App\Exceptions\BookingCapacityException;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BookingController extends Controller
{
    /**
     * Store a new booking for the given event.
     */
    public function store(StoreBookingRequest $request, Event $event): JsonResponse
    {
        try {
            $booking = (new CreateBooking())->handle($event, $request->validated(), $request->ip());
        } catch (BookingCapacityException) {
            return response()->json(
                ['message' => 'No seats remaining for this event.'],
                422
            );
        }

        return response()->json(
            [
                'booking_reference' => $booking->booking_reference,
                'booking_id' => $booking->id,
            ],
            201
        );
    }

    /**
     * Download the Digital Pass for a confirmed booking as a styled HTML page.
     */
    public function downloadPass(Booking $booking): Response
    {
        $booking->loadMissing('event');

        $html = view('bookings.pass', ['booking' => $booking])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => "attachment; filename=\"pass-{$booking->booking_reference}.html\"",
        ]);
    }
}
