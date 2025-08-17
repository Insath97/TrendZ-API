<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    public function index()
    {
        $branch = Shop::with('location')->where(['status' => 1, 'delete_status' => 1])->get();

        if ($branch->isEmpty()) {
            return response()->json(['message' => 'No Data Found'], 200);
        }

        return ShopResource::collection($branch);
    }

    public function create() {}

    public function store(StoreShopRequest $request)
    {
        $branch = Shop::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Branch created successfully',
            'data' => $branch,
        ], 201);
    }

    public function show(string $Shop)
    {
        $branch = Shop::with('location')->find($Shop);

        return response()->json([
            'success' => true,
            'data' => new ShopResource($branch)
        ], 200);
    }

    public function edit(string $id) {}

    public function update(UpdateShopRequest $request, string $id)
    {
        try {
            $branch = Shop::findORFail($id);
            $branch->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Branch updated successfully',
                'data' => $branch,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Failed to update shop'], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $branch = Shop::findORFail($id);
            $branch->delete_status = 0;
            $branch->save();
            return response()->json(['success' => true, 'message' => 'Shop deleted successfully'], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Failed to delete shop'], 500);
        }
    }

    public function getShops()
    {
        try {
            $branch = Shop::where('delete_status', 1)
                ->select('id', 'code', 'name')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Shop data retrieved successfully',
                'data' => $branch
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Failed to get shop data'], 500);
        }
    }

    public function updateBookingFees(Request $request)
    {
        try {
            // Get authenticated merchant
            $merchant = Auth::guard('merchant')->user();

            // Verify merchant owns the shop
            $shop = Shop::findOrFail($merchant->saloon_id);

            // Validate input
            $validated = $request->validate([
                'booking_fees' => 'required|numeric|min:0|max:9999'
            ]);

            $shop->booking_fees = $validated['booking_fees'];
            $shop->save();

            return response()->json([
                'success' => true,
                'message' => 'Booking fees updated successfully',
                'data' => [
                    'shop_id' => $shop->id,
                    'booking_fees' => $shop->booking_fees
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking fees',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
