<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Resources\BarberResource;
use App\Models\Barber;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BarberController extends Controller
{
    use FileUploadTrait;

    public function index()
    {
        $merchant = Auth::user('merchant');

        $barber = Barber::with('shops')->where('saloon_id', $merchant->saloon_id)->latest()->get();

        if ($barber->isEmpty()) {
            return response()->json(['message' => 'No barbers found'], 200);
        }

        return BarberResource::collection($barber);
    }

    public function create() {}

    public function store(Request $request)
    {
        try {
            $merchant = Auth::user('merchant');

            $imagePath = $this->handleFileUpload($request, 'image', null, 'barber', 'barber');

            if (!$imagePath) {
                return redirect()->back()
                    ->with('error', 'Failed to upload image. Please try again.')
                    ->withInput();
            }

            $barber = new Barber();
            $barber->saloon_id = $merchant->saloon_id;
            $barber->name = $request->name;
            $barber->code = $request->code;
            $barber->phone = $request->phone;
            $barber->email = $request->email;
            $barber->image = $imagePath ?? "/image";
            $barber->description = $request->description;
            $barber->save();

            $barber = Barber::with('shops')->find($barber->id);

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
        $merchant = Auth::user('merchant');
        $barber = Barber::with('shops')->where('saloon_id', $merchant->saloon_id)->find($id);


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
            $request->validate([
                'name' => 'nullable|string|unique:barbers,name,' . $id,
                'code' => 'required|string|unique:barbers,code,' . $id,
                'phone' => 'nullable|string',
                'email' => 'nullable|email',
                // Add other validation rules as needed
            ]);

            $merchant = Auth::user('merchant');
            $barber = Barber::with('shops')->findOrFail($id);

            $imagePath = $this->handleFileUpload($request, 'image', null, 'barber', 'barber');

            $barber->saloon_id = $merchant->saloon_id;
            $barber->name = $request->name;
            $barber->code = $request->code;
            $barber->phone = $request->phone;
            $barber->email = $request->email;
            $barber->image = !empty($imagepath) ? $imagepath : $barber->image;
            $barber->description = $request->description;
            $barber->save();

            $barber = Barber::with('shops')->find($barber->id);

            return response()->json([
                'success' => true,
                'message' => 'Barber Updated successfully',
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

    public function toggleActive(string $id)
    {
        try {
            $merchant = Auth::user('merchant');

            $barber = Barber::where('saloon_id', $merchant->saloon_id)->findOrFail($id);

            $barber->is_active = !$barber->is_active;
            $barber->save();

            return response()->json([
                'success' => true,
                'message' => 'Barber active status updated successfully',
                'data' => [
                    'barber_id' => $barber->id,
                    'name' => $barber->name,
                    'status' => $barber->is_active ? 'active' : 'inactive'
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update barber status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleAvailable(string $id)
    {
        try {
            $merchant = Auth::user('merchant');

            $barber = Barber::where('saloon_id', $merchant->saloon_id)->findOrFail($id);

            $barber->is_available = !$barber->is_available;
            $barber->save();

            return response()->json([
                'success' => true,
                'message' => 'Barber availability updated successfully',
                'data' => [
                    'barber_id' => $barber->id,
                    'name' => $barber->name,
                    'availability' => $barber->is_available ? 'available' : 'unavailable'
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update barber availability',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
