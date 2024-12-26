<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShopResource;
use App\Models\Location;
use App\Models\Service;
use App\Models\Shop;
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
}
