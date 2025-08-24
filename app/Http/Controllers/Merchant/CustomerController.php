<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResourse;
use App\Models\Customer;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index()
    {

        $customer = Customer::with('location')->get();

        if ($customer->isEmpty()) {
            return response()->json(['message' => 'No Data Found'], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $customer,
        ], 200);
    }

    /**
     * Get customers for a specific shop
     */
    public function shopCustomers(Request $request)
    {
        try {
            $merchant = Auth::guard('merchant')->user();

            // Verify the shop belongs to the merchant
            $shop = Shop::where('id', $merchant->saloon_id)->first();

            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop not found or unauthorized access'
                ], 404);
            }

            $perPage = $request->get('per_page', 15);
            $search = $request->get('search');
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');

            $customers = Customer::select('customers.*')
                ->join('bookings', 'customers.id', '=', 'bookings.customer_id')
                ->where('bookings.shop_id', $merchant->saloon_id)
                ->when($search, function ($query) use ($search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('customers.name', 'like', "%{$search}%")
                            ->orWhere('customers.email', 'like', "%{$search}%")
                            ->orWhere('customers.phone_number', 'like', "%{$search}%");
                    });
                })
                ->groupBy('customers.id')
                ->withCount(['bookings as total_bookings' => function ($query) use ($merchant) {
                    $query->where('shop_id', $merchant->saloon_id);
                }])
                ->with(['location:id,name'])
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage);


            return response()->json([
                'success' => true,
                'message' => 'Shop customers retrieved successfully',
                'data' => [
                    'shop' => $shop->only(['id', 'name', 'code', 'address']),
                    'customers' => $customers,
                    'stats' => [
                        'total_customers' => $customers->total(),
                        'active_customers' => Customer::join('bookings', 'customers.id', '=', 'bookings.customer_id')
                            ->where('bookings.shop_id', $merchant->saloon_id)
                            ->where('bookings.booking_date', '>=', now()->subDays(30))
                            ->distinct('customers.id')
                            ->count('customers.id'),
                        'new_customers_this_month' => Customer::join('bookings', 'customers.id', '=', 'bookings.customer_id')
                            ->where('bookings.shop_id', $merchant->saloon_id)
                            ->where('bookings.created_at', '>=', now()->startOfMonth())
                            ->distinct('customers.id')
                            ->count('customers.id')
                    ]
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving shop customers',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
