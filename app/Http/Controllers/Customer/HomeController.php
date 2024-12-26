<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Location;
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
}
