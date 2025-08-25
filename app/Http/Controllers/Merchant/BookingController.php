<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Mail\BookingInvoiceMail;
use App\Models\Booking;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    /**
     * Get today's bookings for the merchant's shops
     */
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

    /**
     * Get future bookings (beyond tomorrow) for the merchant's shops
     */
    public function futureBookings(Request $request)
    {
        try {
            $merchant = Auth::guard('merchant')->user();
            $perPage = $request->get('per_page', 15);

            $bookings = Booking::with('customer', 'barber', 'services', 'slots')
                ->where('shop_id', $merchant->saloon_id)
                ->whereDate('booking_date', '>', Carbon::tomorrow())
                ->where('status', 'upcoming')
                ->orderBy('booking_date', 'asc')
                ->orderBy('booking_number', 'asc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Future bookings retrieved successfully',
                'data' => $bookings
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving future bookings',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get all bookings with filters for merchant's shops
     */
    public function allBookings(Request $request)
    {
        try {
            $merchant = Auth::guard('merchant')->user();

            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');

            $bookings = Booking::with('customer', 'barber', 'services', 'slots')
                ->where('shop_id', $merchant->saloon_id)
                ->when($status, function ($query) use ($status) {
                    return $query->where('status', $status);
                })
                ->orderBy('booking_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'All bookings retrieved successfully',
                'data' => $bookings
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving bookings',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Update booking status to processing
     */
    public function startProcessing($id)
    {
        try {
            $merchant = Auth::guard('merchant')->user();

            $booking = Booking::where('shop_id', $merchant->saloon_id)->find($id);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            if ($booking->status !== 'upcoming') {
                return response()->json([
                    'success' => false,
                    'message' => "Booking cannot be processed. Current status: {$booking->status}"
                ], 400);
            }

            if (!Carbon::parse($booking->booking_date)->isToday()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking can only be processed on the scheduled date'
                ], 400);
            }

            $booking->status = 'processing';
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Booking is now being processed',
                'data' => $booking
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating booking status',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Complete a processing booking
     */
    public function completeBooking($id)
    {
        try {
            $merchant = Auth::guard('merchant')->user();

            $booking = Booking::where('shop_id', $merchant->saloon_id)->find($id);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found or unauthorized access'
                ], 404);
            }

            if ($booking->status !== 'processing') {
                return response()->json([
                    'success' => false,
                    'message' => "Booking cannot be completed. Current status: {$booking->status}"
                ], 400);
            }

            $booking->status = 'completed';
            $booking->save();

            Mail::to($booking->customer->email)->send(new BookingInvoiceMail($booking));

            return response()->json([
                'success' => true,
                'message' => 'Booking completed successfully',
                'data' => $booking
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error completing booking',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a booking
     */
    public function cancelBooking($id)
    {
        try {
            $merchant = Auth::guard('merchant')->user();

            $booking = Booking::where('shop_id', $merchant->saloon_id)->find($id);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found or unauthorized access'
                ], 404);
            }

            if (!in_array($booking->status, ['upcoming', 'processing'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Booking cannot be canceled. Current status: {$booking->status}"
                ], 400);
            }

            $booking->status = 'canceled';
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Booking canceled successfully',
                'data' => $booking
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error canceling booking',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get upcoming bookings organized by slots in queue order
     */
    public function upcomingQueue(Request $request)
    {
        try {
            $merchant = Auth::guard('merchant')->user();
            $currentTime = now()->setTimezone('Asia/Colombo');
            $today = $currentTime->format('Y-m-d');

            // Get all active slots for the shop
            $slots = Slot::where('saloon_id', $merchant->saloon_id)
                ->where('is_active', true)
                ->orderBy('start_time')
                ->get();

            $queueData = [];
            $totalUpcoming = 0;
            $currentSlot = null;

            foreach ($slots as $slot) {
                $slotStart = Carbon::parse($slot->start_time);
                $slotEnd = Carbon::parse($slot->end_time);

                // Check if current time is within this slot
                $isCurrentSlot = $currentTime->between($slotStart, $slotEnd);

                // Get bookings for this slot for today
                $bookings = Booking::with([
                    'customer:id,name,phone_number',
                    'barber:id,name',
                    'services:id,name'
                ])
                    ->whereHas('slots', function ($query) use ($slot) {
                        $query->where('slot_id', $slot->id);
                    })
                    ->where('shop_id', $merchant->saloon_id)
                    ->whereDate('booking_date', $today)
                    ->where('status', 'upcoming')
                    ->orderBy('booking_number', 'asc')
                    ->get();

                $slotBookings = [];
                $currentAppointment = 1;

                foreach ($bookings as $booking) {
                    $slotBookings[] = [
                        'id' => $booking->id,
                        'appointment_number' => $booking->booking_number,
                        'customer_name' => $booking->customer->name,
                        'customer_phone' => $booking->customer->phone_number,
                        'barber_name' => $booking->barber->name ?? 'Not assigned',
                        'services' => $booking->services->pluck('name'),
                        'total_amount' => $booking->total_amount,
                        'current_position' => $currentAppointment++
                    ];
                }

                $slotData = [
                    'slot_id' => $slot->id,
                    'slot_name' => $slot->slot_name,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'current_time' => $currentTime->format('H:i:s'),
                    'is_current_slot' => $isCurrentSlot,
                    'bookings' => $slotBookings,
                    'total_bookings' => count($slotBookings),
                    'available_slots' => max(0, $slot->max_bookings - count($slotBookings))
                ];

                $queueData[] = $slotData;
                $totalUpcoming += count($slotBookings);

                // Set current slot if this is the active one
                if ($isCurrentSlot) {
                    $currentSlot = $slotData;
                }
            }

            // Get current processing booking if any
            $currentProcessing = Booking::with(['customer:id,name', 'barber:id,name'])
                ->where('shop_id', $merchant->saloon_id)
                ->where('status', 'processing')
                ->whereDate('booking_date', $today)
                ->first();

            return response()->json([
                'success' => true,
                'message' => "Today's upcoming queue retrieved successfully",
                'data' => [
                    'date' => $today,
                    'current_time' => $currentTime->format('H:i:s'),
                    'total_upcoming_bookings' => $totalUpcoming,
                    'current_processing' => $currentProcessing ? [
                        'id' => $currentProcessing->id,
                        'appointment_number' => $currentProcessing->booking_number,
                        'customer_name' => $currentProcessing->customer->name,
                        'barber_name' => $currentProcessing->barber->name ?? 'Not assigned',
                        'started_at' => $currentProcessing->processing_started_at?->format('H:i:s')
                    ] : null,
                    'current_slot' => $currentSlot,
                    'all_slots' => $queueData
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving upcoming queue',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
