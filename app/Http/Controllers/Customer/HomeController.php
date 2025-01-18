<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShopResource;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Location;
use App\Models\Service;
use App\Models\Shop;
use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        $shop = Shop::with('location')->find($id);

        return response()->json([
            'success' => true,
            'data' => new ShopResource($shop)
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
                ->count() + 1;

            $booking = new Booking();
            $booking->customer_id = auth('customer')->id();
            $booking->shop_id = $request->shop_id;
            $booking->booking_date = $request->booking_date;
            $booking->unique_reference = $bookingNumber;
            $booking->booking_number = $appoinment_number;
            $booking->total_amount = $request->total_amount;
            $booking->save();

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

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'booking' => $booking,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Error creating booking', 'error' => $th->getMessage()], 500);
        }
    }

    public function check()
    {
        $booking = Booking::with('services')->get();

        return response()->json([
            'success' => true,
            'message' => 'API is working',
            'data' => $booking
        ], 200);
    }
}
