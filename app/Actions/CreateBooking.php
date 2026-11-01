<?php

namespace App\Actions;

use App\Exceptions\BookingCapacityException;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class CreateBooking
{
    /**
     * Create a new pending booking for the given event.
     *
     * Capacity is reserved immediately so the slot cannot be sold twice while
     * the STK push is outstanding. booked_slots is released if payment fails.
     *
     * @param  array<string, mixed>  $data  Validated data from StoreBookingRequest
     *
     * @throws BookingCapacityException
     */
    public function handle(Event $event, array $data, string $ipAddress): Booking
    {
        return DB::transaction(function () use ($event, $data, $ipAddress): Booking {
            /** @var Event $lockedEvent */
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->id);

            if ($lockedEvent->available_slots < $data['quantity']) {
                throw new BookingCapacityException;
            }

            /** @var Booking $booking */
            $booking = Booking::create([
                'event_id' => $lockedEvent->id,
                'contact_name' => $data['contact_name'],
                'contact_email' => $data['contact_email'],
                'contact_phone' => $data['contact_phone'],
                'quantity' => $data['quantity'],
                'total_price' => $lockedEvent->price * $data['quantity'],
                'payment_status' => 'pending',
            ]);

            $lockedEvent->increment('booked_slots', $data['quantity']);

            foreach ($data['responses'] ?? [] as $response) {
                $booking->responses()->create([
                    'event_question_id' => $response['event_question_id'],
                    'answer' => $response['answer'],
                ]);
            }

            if (
                ! empty($data['consent']['agreed']) &&
                $lockedEvent->liability_waiver_text !== null
            ) {
                $booking->consent()->create([
                    'agreed_terms_text' => $lockedEvent->liability_waiver_text,
                    'signer_name' => $data['consent']['signer_name'],
                    'signer_ip_address' => $ipAddress,
                    'signed_at' => now(),
                ]);
            }

            return $booking;
        });
    }
}
