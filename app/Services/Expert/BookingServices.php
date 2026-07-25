<?php

namespace App\Services\Expert;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingServices
{
    public function getExpertBookings()
    {
        $expert = Auth::user();

        return Booking::where('provider_type', 'expert')
            ->where('provider_id', $expert->id)
            ->with(['user', 'employee', 'bookingServices.service', 'payment'])
            ->latest()
            ->paginate(10);
    }

    public function getBookingDetails(Booking $booking): Booking
    {
        $this->authorizeBooking($booking);

        return $booking->load(['user', 'employee', 'bookingServices.service', 'payment']);
    }

    public function cancelBooking(Booking $booking, array $data): Booking
    {
        $this->authorizeBooking($booking);

        DB::transaction(function () use ($booking, $data) {
            $booking->update([
                'status'              => 'cancelled',
                'cancelled_by'        => 'provider',
                'cancellation_reason' => $data['cancellation_reason'],
            ]);

            $payment = $booking->payment;

            if ($payment && $payment->status === 'paid') {
                $payment->update(['status' => 'refunded']);
            }
        });

        return $booking->fresh(['payment']);
    }

    protected function authorizeBooking(Booking $booking): void
    {
        $expert = Auth::user();

        if ($booking->provider_type !== 'expert' || (int) $booking->provider_id !== (int) $expert->id) {
            throw new NotFoundHttpException('Booking not found.');
        }
    }
}
