<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        $branch = Shop::where('delete_status', 1)->get();

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
        $branch = Shop::find($Shop);

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
}
