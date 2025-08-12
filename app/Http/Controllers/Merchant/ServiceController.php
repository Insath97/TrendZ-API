<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function index()
    {
        /* get particular merchant data */
        $merchant = Auth::user('merchant');

        $services = Service::where('saloon_id', $merchant->saloon_id)
            ->where(['status' => 1, 'delete_status' => 1])
            ->get();

        if ($services->isEmpty()) {
            return response()->json(['message' => 'No Data Found'], 200);
        }

        return ServiceResource::collection($services);
    }

    public function create() {}

    public function store(StoreServiceRequest $request)
    {
        $merchant = Auth::user('merchant');

        $services = new Service();
        $services->saloon_id = $merchant->saloon_id;
        $services->code = $request->code;
        $services->name = $request->name;
        $services->icon = $request->icon;
        $services->description = $request->description;
        $services->price = $request->price;
        $services->duration = $request->duration;
        $services->save();

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'data' => new ServiceResource($services),
        ], 201);
    }

    public function show(string $service)
    {
        $merchant = Auth::user('merchant');
        $service = Service::where('saloon_id', $merchant->saloon_id)->find($service);

        return response()->json([
            'success' => true,
            'data' => new ServiceResource($service),
        ], 200);
    }

    public function edit(string $id) {}

    public function update(UpdateServiceRequest $request, string $id)
    {
        $merchant = Auth::user('merchant');

        $services = Service::where('saloon_id', $merchant->saloon_id)->findOrFail($id);
        $services->saloon_id = $merchant->saloon_id;
        $services->code = $request->code;
        $services->name = $request->name;
        $services->icon = $request->icon;
        $services->description = $request->description;
        $services->price = $request->price;
        $services->duration = $request->duration;
        $services->save();

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'data' => new ServiceResource($services),
        ], 200);
    }

    public function destroy(string $id)
    {
        try {
            $merchant = Auth::user('merchant');

            $services = Service::where('saloon_id', $merchant->saloon_id)->findOrFail($id);
            $services->delete_status = 0;
            $services->save();

            return response()->json([
                'success' => true,
                'message' => 'Service deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Failed to delete service'], 500);
        }
    }

    public function deactivateService(string $id)
    {
        try {
            $merchant = Auth::user('merchant');

            $service = Service::where('saloon_id', $merchant->saloon_id)->findOrFail($id);

            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not found.',
                ], 404);
            }

            $service->status = $service->status === 1 ? 0 : 1;
            $service->save();

            return response()->json([
                'success' => true,
                'message' => 'Service status updated successfully.',
                'data' => [
                    'service_id' => $service->id,
                    'name' => $service->name,
                    'status' => $service->status ? 'active' : 'inactive',
                ],
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the service status.',
            ], 500);
        }
    }
}
