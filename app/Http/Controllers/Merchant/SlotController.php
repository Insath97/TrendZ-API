<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Resources\SlotResource;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SlotController extends Controller
{
    public function index()
    {
        $merchant = Auth::user('merchant');

        $slot = Slot::where('saloon_id', $merchant->saloon_id)
            ->where(['is_active' => true, 'delete_status' => 1])
            ->get();

        if ($slot->isEmpty()) {
            return response()->json(['message' => 'No Data Found'], 200);
        }

        return SlotResource::collection($slot);
    }

    public function create() {}

    public function store(Request $request)
    {
        $merchant = Auth::user('merchant');

        $slot = new Slot();
        $slot->saloon_id = $merchant->saloon_id;
        $slot->slot_name = $request->slot_name;
        $slot->start_time = $request->start_time;
        $slot->end_time = $request->end_time;
        $slot->max_bookings = $request->max_bookings;
        $slot->save();

        return response()->json([
            'success' => true,
            'message' => 'Slot created successfully',
            'data' => new SlotResource($slot),
        ], 201);
    }

    public function show(string $slot)
    {
        $merchant = Auth::user('merchant');
        $slot = Slot::where('saloon_id', $merchant->saloon_id)->find($slot);

        return response()->json([
            'success' => true,
            'data' => new SlotResource($slot),
        ], 200);
    }

    public function edit(string $id) {}

    public function update(Request $request, string $id)
    {
        $merchant = Auth::user('merchant');
        $slot = Slot::where('saloon_id', $merchant->saloon_id)->findOrFail($id);
        $slot->saloon_id = $merchant->saloon_id;
        $slot->slot_name = $request->slot_name;
        $slot->start_time = $request->start_time;
        $slot->end_time = $request->end_time;
        $slot->max_bookings = $request->max_bookings;
        $slot->is_active = $slot->is_active === 1 ? 0 : 1;
        $slot->save();

        return response()->json([
            'success' => true,
            'message' => 'Slot updated successfully',
            'data' => new SlotResource($slot),
        ], 200);
    }

    public function destroy(string $id)
    {
        try {
            $merchant = Auth::user('merchant');
            $slot = Slot::where('saloon_id', $merchant->saloon_id)->findOrFail($id);
            $slot->delete();

            return response()->json([
                'success' => true,
                'message' => 'Slot deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Failed to delete slot'], 500);
        }
    }

    public function deactivateSlot(string $id)
    {
        try {
            $merchant = Auth::user('merchant');
            $slot = Slot::where('saloon_id', $merchant->saloon_id)->findOrFail($id);

            if (!$slot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slot not found.',
                ], 404);
            }

            $slot->is_active = $slot->is_active === 1 ? 0 : 1;
            $slot->save();

            return response()->json([
                'success' => true,
                'message' => 'Slot status updated successfully.',
                'data' => [
                    'slot_id' => $slot->id,
                    'slot_name' => $slot->slot_name,
                    'status' => $slot->is_active ? 'active' : 'inactive',
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the slot status.',
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $merchant = Auth::user('merchant');
            $slot = Slot::where('saloon_id', $merchant->saloon_id)->findOrFail($id);
            $slot->delete_status = 1;
            $slot->save();

            return response()->json([
                'success' => true,
                'message' => 'Slot restore successfully',
                'data' => $slot
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Failed to restore slot'], 500);
        }
    }
}
