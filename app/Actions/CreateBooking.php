<?php

namespace App\Actions;

use App\Exceptions\BookingCapacityException;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class CreateBooking
{
    /**
     * Create a new booking for the given event.
     *
     * Checks capacity before entering the transaction, then atomically creates
     * the Booking, BookingResponse records, and an optional Consent record.
     * booked_slots is NOT incremented here — that only happens when payment
     * transitions to 'paid' via markAsPaid().
     *
     * @param  array<string, mixed>  $data  Validated data from StoreBookingRequest
     *
     * @throws BookingCapacityException
     */
    public function handle(Event $event, array $data, string $ipAddress): Booking
    {
        if ($event->available_slots < $data['quantity']) {
            throw new BookingCapacityException;
        }

        return DB::transaction(function () use ($event, $data, $ipAddress): Booking {
            /** @var Booking $booking */
            $booking = Booking::create([
                'event_id' => $event->id,
                'contact_name' => $data['contact_name'],
                'contact_email' => $data['contact_email'],
                'contact_phone' => $data['contact_phone'],
                'quantity' => $data['quantity'],
                'total_price' => $event->price * $data['quantity'],
                'payment_status' => 'pending',
            ]);

            foreach ($data['responses'] ?? [] as $response) {
                $booking->responses()->create([
                    'event_question_id' => $response['event_question_id'],
                    'answer' => $response['answer'],
                ]);
            }

            if (
                ! empty($data['consent']['agreed']) &&
                $event->liability_waiver_text !== null
            ) {
                $booking->consent()->create([
                    'agreed_terms_text' => $event->liability_waiver_text,
                    'signer_name' => $data['consent']['signer_name'],
                    'signer_ip_address' => $ipAddress,
                    'signed_at' => now(),
                ]);
            }

            return $booking;
        });
    }

    /**
     * Transition a booking to 'paid' and increment the event's booked_slots.
     *
     * Uses a lockForUpdate() on the event row to prevent concurrent over-booking.
     */
    public static function markAsPaid(Booking $booking): void
    {
        DB::transaction(function () use ($booking): void {
            $booking->payment_status = 'paid';
            $booking->save();

            Event::where('id', $booking->event_id)
                ->lockForUpdate()
                ->increment('booked_slots', $booking->quantity);
        });
    }
}
