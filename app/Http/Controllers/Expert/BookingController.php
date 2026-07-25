<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expert\CancelBookingRequest;
use App\Models\Booking;
use App\Services\Expert\BookingServices;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected BookingServices $bookingService) {}

    public function index()
    {
        $bookings = $this->bookingService->getExpertBookings();

        return $this->sendResponse([
            'bookings'        => $bookings->items(),
            'nextPageUrl'     => $bookings->nextPageUrl(),
            'previousPageUrl' => $bookings->previousPageUrl(),
        ], 'Bookings retrieved successfully.');
    }

    public function show(Booking $booking)
    {
        $booking = $this->bookingService->getBookingDetails($booking);

        return $this->sendResponse(['booking' => $booking], 'Booking details retrieved successfully.');
    }

    public function destroy(CancelBookingRequest $request, Booking $booking)
    {
        $booking = $this->bookingService->cancelBooking($booking, $request->validated());

        return $this->sendResponse(['booking' => $booking], 'Booking cancelled and refund processed.');
    }
}
