<?php

namespace App\Http\Controllers;

use App\Actions\CreateBooking;
use App\Actions\InitiateStkPush;
use App\Exceptions\BookingCapacityException;
use App\Exceptions\MpesaException;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Event;
use App\Services\Mpesa\DarajaClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Store a new booking for the given event and send an STK push.
     */
    public function store(
        StoreBookingRequest $request,
        Event $event,
        CreateBooking $createBooking,
        InitiateStkPush $initiateStkPush,
        DarajaClient $daraja,
    ): JsonResponse {
        abort_unless($event->status === 'published', 404);

        if (! $daraja->isConfigured()) {
            throw new MpesaException('M-Pesa is not configured on this server.');
        }

        try {
            $booking = $createBooking->handle($event, $request->validated(), (string) $request->ip());
        } catch (BookingCapacityException) {
            return response()->json(
                ['message' => 'No slots remaining for this event.'],
                422
            );
        }

        $transaction = $initiateStkPush->handle(
            $booking,
            $request->validated('mpesa_phone'),
            $booking->booking_reference,
            Str::limit($event->title, 20, ''),
        );

        return response()->json(
            [
                'booking_reference' => $booking->booking_reference,
                'booking_id' => $booking->id,
                'payment_id' => $transaction->uuid,
                'payment_status' => $transaction->status,
                'message' => 'Check your phone and enter your M-Pesa PIN to complete payment.',
            ],
            201
        );
    }

    /**
     * Cancel an existing booking.
     */
    public function cancel(Booking $booking): JsonResponse
    {
        if ($booking->payment_status === 'cancelled') {
            return response()->json([
                'message' => 'Booking is already cancelled.',
                'booking' => $booking,
            ], 422);
        }

        DB::transaction(function () use ($booking): void {
            /** @var Booking $locked */
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if (in_array($locked->payment_status, ['pending', 'paid'], true)) {
                Event::where('id', $locked->event_id)
                    ->where('booked_slots', '>=', $locked->quantity)
                    ->decrement('booked_slots', $locked->quantity);
            }

            $locked->payment_status = 'cancelled';
            $locked->save();
        });

        return response()->json([
            'message' => 'Booking cancelled successfully.',
            'booking' => $booking->fresh(),
        ]);
    }

    /**
     * Download the Digital Pass for a confirmed booking as a PDF.
     */
    public function downloadPass(Booking $booking): Response
    {
        abort_unless($booking->payment_status === 'paid', 403);

        $booking->loadMissing('event');

        $pdf = Pdf::loadView('bookings.pass-pdf', ['booking' => $booking]);

        return $pdf->download("pass-{$booking->booking_reference}.pdf");
    }
}
