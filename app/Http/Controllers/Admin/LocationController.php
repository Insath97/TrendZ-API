<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $location = Location::where(['status' => 1, 'delete_status' => 1])->get();

        if ($location->isEmpty()) {
            return response()->json(['message' => 'No Data Found'], 200);
        }

        return LocationResource::collection($location);
    }

    public function create() {}

    public function store(StoreLocationRequest $request)
    {
        $location = Location::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Branch created successfully',
            'data' => $location,
        ], 201);
    }

    public function show(string $location)
    {
        $location = Location::find($location);

        return response()->json([
            'success' => true,
            'data' => new LocationResource($location)
        ], 200);
    }

    public function edit(string $id) {}

    public function update(UpdateLocationRequest $request, string $id)
    {
        try {
            $location = Location::findORFail($id);
            $location->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully',
                'data' => $location,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Failed to update location'], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $location = Location::findORFail($id);
            $location->delete_status = 0;
            $location->save();
            return response()->json(['success' => true, 'message' => 'Location deleted successfully'], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Failed to delete location'], 500);
        }
    }
}
