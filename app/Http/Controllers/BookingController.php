<?php

namespace App\Http\Controllers;

use App\Actions\CreateBooking;
use App\Exceptions\BookingCapacityException;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
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
            $booking = (new CreateBooking)->handle($event, $request->validated(), $request->ip());
        } catch (BookingCapacityException) {
            return response()->json(
                ['message' => 'No slots remaining for this event.'],
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
     * Download the Digital Pass for a confirmed booking as a PDF.
     */
    public function downloadPass(Booking $booking): Response
    {
        $booking->loadMissing('event');

        $pdf = Pdf::loadView('bookings.pass-pdf', ['booking' => $booking]);

        return $pdf->download("pass-{$booking->booking_reference}.pdf");
    }
}
