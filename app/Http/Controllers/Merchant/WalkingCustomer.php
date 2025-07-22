<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalkingCustomer as ResourcesWalkingCustomer;
use App\Models\Service;
use App\Models\WalkingCustomer as ModelsWalkingCustomer;
use Illuminate\Http\Request;

class WalkingCustomer extends Controller
{
    public function index()
    {
        $customers = ModelsWalkingCustomer::with('services')->latest()->get();

        if ($customers->isEmpty()) {
            return response()->json(['message' => 'No walking customers found'], 200);
        }

        return ResourcesWalkingCustomer::collection($customers);
    }

    public function create() {}

    public function store(Request $request)
    {
        try {
            $customer = new ModelsWalkingCustomer();
            $customer->fullname = $request->fullname;
            $customer->age = $request->age;
            $customer->phone_number = $request->phone_number;
            $customer->gender = $request->gender;
            $customer->address = $request->address;
            $customer->email = $request->email;
            $customer->occupation = $request->occupation;
            $customer->save();

            // Attach services if provided
            if ($request->has('services')) {
                $customer->services()->attach($request->services);
            }

            return response()->json([
                'success' => true,
                'message' => 'Walking customer created successfully',
                'data' => new ResourcesWalkingCustomer($customer),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create walking customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        $customer = ModelsWalkingCustomer::with('services')->find($id);
        return response()->json([
            'success' => true,
            'data' => new ResourcesWalkingCustomer($customer)
        ], 200);
    }

    public function edit(string $id) {}

    public function update(Request $request, string $id)
    {
        try {
            $customer = ModelsWalkingCustomer::with('services')->findOrFail($id);
            $customer->fullname = $request->fullname;
            $customer->age = $request->age;
            $customer->phone_number = $request->phone_number;
            $customer->gender = $request->gender;
            $customer->address = $request->address;
            $customer->email = $request->email;
            $customer->occupation = $request->occupation;
            $customer->save();

            // Sync services if provided
            if ($request->filled('services')) {
                $validatedServices = Service::whereIn('id', $request->services)->pluck('id')->toArray();
                $customer->services()->sync($validatedServices);
            }

            return response()->json([
                'success' => true,
                'message' => 'Walking customer updated successfully',
                'data' => new ResourcesWalkingCustomer($customer),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update walking customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $customer = ModelsWalkingCustomer::with('services')->findOrFail($id);
            $customer->delete();

            $customer->services()->detach();

            return response()->json([
                'success' => true,
                'message' => 'Walking customer deleted successfully',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete walking customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
