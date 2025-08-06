<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Resources\BarberResource;
use App\Models\Barber;
use Illuminate\Http\Request;
use App\Traits\FileUploadTrait;

class BarberController extends Controller
{
    use FileUploadTrait;

    public function index()
    {
        $barber = Barber::with('shop')->latest()->get();

        if ($barber->isEmpty()) {
            return response()->json(['message' => 'No barbers found'], 200);
        }

        return BarberResource::collection($barber);
    }

    public function create() {}

    public function store(Request $request)
    {
        try {
            $imagePath = $this->handleFileUpload($request, 'image', null, 'barber', 'barber');

            if (!$imagePath) {
                return redirect()->back()
                    ->with('error', 'Failed to upload image. Please try again.')
                    ->withInput();
            }

            $barber = new Barber();
            $barber->saloon_id = $request->saloon_id;
            $barber->name = $request->name;
            $barber->code = $request->code;
            $barber->phone = $request->phone;
            $barber->email = $request->email;
            $barber->image = $imagePath ?? "/image";
            $barber->description = $request->description;
            $barber->save();

            $barber = Barber::with('saloon')->find($barber->id);

            return response()->json([
                'success' => true,
                'message' => 'Barber created successfully',
                'data' => $barber,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create barber',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        $barber = Barber::with('saloon')->find($id);


        if (!$barber) {
            return response()->json([
                'success' => false,
                'message' => 'Barber not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new BarberResource($barber)
        ], 200);
    }

    public function edit(string $id) {}

    public function update(Request $request, string $id)
    {
        try {
            $barber = Barber::with('shop')->findOrFail($id);
            $barber->saloon_id = $request->saloon_id;
            $barber->name = $request->name;
            $barber->code = $request->code;
            $barber->phone = $request->phone;
            $barber->email = $request->email;
            $barber->image = $request->image;
            $barber->description = $request->description;
            $barber->save();

            $updatedBarber = Barber::with('shop')->find($barber->id);

            return response()->json([
                'success' => true,
                'message' => 'Barber updated successfully',
                'data' => $updatedBarber,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update barber',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $barber = Barber::with('saloon')->findOrFail($id);
            $barber->delete();

            return response()->json([
                'success' => true,
                'message' => 'Barber deleted successfully',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete barber',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
