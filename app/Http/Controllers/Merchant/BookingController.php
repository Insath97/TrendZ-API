<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function todayBookings(Request $request)
    {
        try {
            $merchant = Auth::guard('merchant')->user();

            $perPage = $request->get('per_page', 15);

            $booking = Booking::with('customer', 'barber', 'services', 'slots')->where('shop_id', $merchant->saloon_id)
                ->whereDate('booking_date', Carbon::today())
                ->where('status', 'upcoming')
                ->orderBy('booking_date', 'asc')
                ->orderBy('booking_number', 'asc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Today\'s bookings retrieved successfully',
                'data' => $booking
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving today\'s bookings',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get tomorrow's bookings for the merchant's shops
     */
    public function tomorrowBookings(Request $request)
    {
        try {
            $merchant = Auth::guard('merchant')->user();

            $perPage = $request->get('per_page', 15);

            $booking = Booking::with('customer', 'barber', 'services', 'slots')->where('shop_id', $merchant->saloon_id)
                ->whereDate('booking_date', Carbon::tomorrow())
                ->where('status', 'upcoming')
                ->orderBy('booking_date', 'asc')
                ->orderBy('booking_number', 'asc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Tomorrow\'s bookings retrieved successfully',
                'data' => $booking
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving tomorrow\'s bookings',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
