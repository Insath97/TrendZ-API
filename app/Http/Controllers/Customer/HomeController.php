<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelBookingRequest;
use App\Http\Requests\CreateBookingRequest;
use App\Http\Resources\ShopResource;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\BookingSlots;
use App\Models\Location;
use App\Models\Service;
use App\Models\Shop;
use App\Models\Slot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isEmpty;

class HomeController extends Controller
{
    public function getLocation()
    {
        $locations = Location::where(['status' => 1, 'delete_status' => 1])->select('id', 'name')->get();

        if ($locations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No locations found',
                'data' => []
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Locations retrieved successfully',
            'data' => $locations
        ], 200);
    }

    public function cusShops(string $id)
    {
        $shop = Shop::with('location', 'services', 'barbers', 'slots')->find($id);

        return response()->json([
            'success' => true,
            'data' => $shop
        ], 200);
    }

    public function cusServices(string $id)
    {
        $shop = Shop::find($id);

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found',
                'data' => []
            ], 404);
        }

        $services = Service::where('saloon_id', $id)->where(['status' => 1, 'delete_status' => 1])->get();

        if ($services->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No services found',
                'data' => []
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Services retrieved successfully',
            'data' => $services
        ], 200);
    }

    public function cusSlots(string $id)
    {
        $shop = Shop::find($id);

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found',
                'data' => []
            ], 404);
        }

        $slots = Slot::where('saloon_id', $id)->where(['is_active' => 1, 'delete_status' => 1])->get();

        if ($slots->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No slots found',
                'data' => []
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Slots retrieved successfully',
            'data' => $slots
        ], 200);
    }

    public function createBooking(Request $request)
    {
        try {

            $total_amount = 0;
            $service_ids = [];

            /* validate slots */
            $slot = Slot::find($request->slot_id);

            if (!$slot) {
                return response()->json(['success' => false, 'message' => 'Slot not found'], 404);
            }

            $current_booking_count = BookingSlots::where('slot_id', $slot->id)
                ->whereHas('booking', function ($query) use ($request) {
                    $query->where('booking_date', $request->booking_date)
                        ->where('status', 'upcoming');
                })
                ->count();

            if ($current_booking_count >= $slot->max_bookings) {
                // Automatically set is_recurring to false
                $slot->is_recurring = false;
                $slot->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Slot is fully booked for the selected day.'
                ], 400);
            }

            /* create reference number format => shopecode+YY+MM=0001 */

            /* 1. get shop code */
            $shop = Shop::find($request->shop_id);
            $shopcode = $shop->code;

            /* 2. get year and month */
            $year = now()->format('y');
            $month = now()->format('m');

            /* 3. get count of the shop booking */
            $count = Booking::where('shop_id', $request->shop_id)
                ->whereYear('created_at', now()->format('Y'))
                ->whereMonth('created_at', now()->format('m'))
                ->count() + 1;

            /* 4. create booking number */
            $bookingNumber = $shopcode . $year . $month . str_pad($count, 4, '0', STR_PAD_LEFT);

            /* get appoinment number it will reset day by day*/
            $startOfDay = Carbon::today();
            $endOfDay = Carbon::tomorrow();

            $appoinment_number = Booking::where('shop_id', $request->shop_id)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('status', 'upcoming')
                ->count() + 1;



            /* total amount calculation */
            if (!empty($request->services) && is_array($request->services)) {
                foreach ($request->services as $service) {
                    // Get service price from database to ensure accuracy
                    $service_model = Service::find($service['service_id']);

                    if (!$service_model) {
                        return response()->json([
                            'success' => false,
                            'message' => "Service ID {$service['service_id']} not found",
                        ], 422);
                    }

                    if (!$service_model->status || !$service_model->delete_status) {
                        return response()->json([
                            'success' => false,
                            'message' => "Service '{$service_model->name}' is not available",
                        ], 422);
                    }

                    $total_amount += $service_model->price;
                    $service_ids[] = $service_model->id;
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or missing services data',
                ], 422);
            }

            if ($shop->booking_fees > 0) {
                $total_amount += $shop->booking_fees;
            }

            if (abs($total_amount - $request->total_amount) > 0.01) { // Allow for small floating point differences
                return response()->json([
                    'success' => false,
                    'message' => 'Total amount mismatch',
                    'details' => [
                        'calculated_total' => $total_amount,
                        'requested_total' => $request->total_amount,
                        'difference' => abs($total_amount - $request->total_amount)
                    ]
                ], 422);
            }

            $customer = Auth::guard('customer')->user();

            $booking = new Booking();
            $booking->customer_id = $customer->id;
            $booking->shop_id = $request->shop_id;
            $booking->barber_id =  $request->barber_id ?? null;
            $booking->booking_date = $request->booking_date;
            $booking->unique_reference = $bookingNumber;
            $booking->booking_number = $appoinment_number;
            $booking->total_amount = $total_amount;
            $booking->save();

            /* services get */
            if (!empty($request->services) && is_array($request->services)) {
                foreach ($request->services as $service) {
                    $service_model = Service::find($service['service_id']);

                    BookingService::create([
                        'booking_id' => $booking->id,
                        'service_id' => $service['service_id'],
                        'total_amount ' => $service_model->price,
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or missing services data',
                ], 422);
            }

            /* slots get */
            BookingSlots::create([
                'booking_id' => $booking->id,
                'slot_id' => $request->slot_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'booking' => $booking,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Error creating booking', 'error' => $th->getMessage()], 500);
        }
    }

    public function cancelBooking(string $id, CancelBookingRequest $request)
    {
        try {
            $booking = Booking::find($id);
            $customer = Auth::guard('customer')->user();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found.'
                ], 404);
            }

            // Check if the booking belongs to the authenticated customer
            if ($booking->customer_id !== $customer->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            if ($booking->status === 'canceled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking is already canceled.'
                ], 400);
            }

            $booking->update([
                'status' => 'canceled',
                'cancellation_reason' => $request->cancellation_reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking has been canceled successfully.',
                'booking' => $booking
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error canceling booking',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function rescheduleBooking(Request $request, string $id)
    {
        try {
            $booking = Booking::findOrFail($id);

            if ($booking->status == 'canceled') {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Canceled bookings cannot be rescheduled.'
                    ],
                    400
                );
            }

            $slot = Slot::find($request->slot_id);

            if (!$slot) {
                return response()->json(['success' => false, 'message' => 'Slot not found'], 404);
            }

            $current_booking_count = BookingSlots::where('slot_id', $slot->id)
                ->whereHas('booking', function ($query) use ($request) {
                    $query->where('booking_date', $request->booking_date)
                        ->where('status', 'upcoming');
                })
                ->count();

            if ($current_booking_count >= $slot->max_bookings) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'The selected slot is fully booked.'
                    ],
                    400
                );
            }

            $startOfDay = Carbon::parse($request->new_booking_date)->startOfDay();
            $endOfDay = Carbon::parse($request->new_booking_date)->endOfDay();

            $new_appointment_number = Booking::where('shop_id', $booking->shop_id)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('status', 'upcoming')
                ->count() + 1;

            $booking->booking_date = $request->new_booking_date;
            $booking->status = 'upcoming';
            $booking->booking_number = $new_appointment_number;
            $booking->save();

            BookingSlots::where('booking_id', $booking->id)->update([
                'slot_id' => $request->new_slot_id,
            ]);

            if ($request->has('services') && count($request->services) > 0) {

                BookingService::where('booking_id', $booking->id)->delete();

                if (!empty($request->services) && is_array($request->services)) {
                    foreach ($request->services as $service) {
                        BookingService::create([
                            'booking_id' => $booking->id,
                            'service_id' => $service['service_id'],
                            'total_amount ' => $request->total_amount,
                        ]);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or missing services data',
                    ], 422);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking successfully rescheduled.',
                'booking' => $booking,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error reshedule booking',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function pendingBooking()
    {
        try {

            if (!auth('customer')->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }

            $customer = Auth::guard('customer')->user();

            $bookings = Booking::with('services', 'slots')
                ->where('customer_id', $customer->id)
                ->where('status', 'upcoming')
                ->with('services', 'slots')
                ->get();

            if ($bookings->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No pending bookings found',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pending bookings retrieved successfully',
                'data' => $bookings
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching pending bookings',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function completedBooking()
    {
        try {

            if (!auth('customer')->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }

            $customer = Auth::guard('customer')->user();

            $bookings = Booking::with('services', 'slots')
                ->where('customer_id', $customer->id)
                ->where('status', 'completed')
                ->with('services', 'slots')
                ->get();

            if ($bookings->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No completed bookings found',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Completed bookings retrieved successfully',
                'data' => $bookings
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching completed bookings',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function cancelledBooking()
    {
        try {

            if (!auth('customer')->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }

            $customer_id = auth('customer')->id();

            $bookings = Booking::with('services', 'slots')
                ->where('customer_id', $customer_id)
                ->where('status', 'canceled')
                ->with('services', 'slots')
                ->get();

            if ($bookings->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No canceled bookings found',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Canceled bookings retrieved successfully',
                'data' => $bookings
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching canceled bookings',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function allBooking()
    {
        try {

            if (!auth('customer')->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }

            $customer = Auth::guard('customer')->user();

            $bookings = Booking::with('services', 'slots', 'barber')
                ->where('customer_id', $customer->id)
                ->with('services', 'slots')
                ->get();

            if ($bookings->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No bookings found',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bookings retrieved successfully',
                'data' => $bookings
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching bookings',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function check()
    {
        $booking = Booking::with('services', 'slots')->get();

        return response()->json([
            'success' => true,
            'message' => 'API is working',
            'data' => $booking
        ], 200);
    }
}
