<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMerchantRequest;
use App\Http\Requests\UpdateMerchantRequest;
use App\Http\Resources\MerchantResource;
use App\Mail\MerchantCredentialsMail;
use App\Models\Merchant;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MerchantController extends Controller
{

    public function index()
    {
        $merchant = Merchant::with('shop')->where('status', 1)->get();

        if ($merchant->isEmpty()) {
            return response()->json(['message' => 'No Data Found'], 200);
        }

        return MerchantResource::collection($merchant);
    }

    public function create() {}

    public function store(StoreMerchantRequest $request)
    {
        try {
            $merchant = new Merchant();
            $merchant->saloon_id = $request->saloon_id;
            $merchant->image = "/image";
            $merchant->name = $request->name;
            $merchant->email = $request->email;
            $merchant->password = bcrypt($request->password);
            $merchant->save();

            $branch = Shop::findOrFail($request->saloon_id);
            $branchName = $branch ? $branch->name : 'Unknown Branch';

            /* send mail */
          /*   Mail::to($request->email)->send(new MerchantCredentialsMail($request->email, $request->password, $request->name, $branchName));
 */
            return response()->json([
                'success' => true,
                'message' => 'Merchant created successfully',
                'data' => $merchant,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Failed to created merchant'], 500);
        }
    }

    public function show(string $merchant)
    {
        $merchant = Merchant::with('shop')->find($merchant);

        return response()->json([
            'success' => true,
            'data' => new MerchantResource($merchant)
        ], 200);
    }

    public function edit(string $id) {}

    public function update(UpdateMerchantRequest $request, string $id)
    {
        try {

            $merchant = Merchant::findOrFail($id);
            $merchant->saloon_id = $request->saloon_id;
            $merchant->image = "/image";
            $merchant->name = $request->name;
            $merchant->email = $request->email;
            if ($request->has('password') && !empty($request->password)) {
                $merchant->password = bcrypt($request->password);
            }
            $merchant->save();

            return response()->json([
                'success' => true,
                'message' => 'Merchant updated successfully',
                'data' => $merchant,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Failed to update merchant'], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $merchant = Merchant::findOrFail($id);
            $merchant->delete();
            return response()->json(['success' => true, 'message' => 'Merchant deleted successfully'], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Failed to delete merchant'], 500);
        }
    }

    public function MerchantToggle(string $id)
    {
        try {
            $merchant = Merchant::findOrFail($id);

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.',
                ], 404);
            }

            // Toggle the status (1 = active, 0 = inactive)
            $merchant->status = $merchant->status === 1 ? 0 : 1;
            $merchant->save();

            return response()->json([
                'success' => true,
                'message' => 'Merchant status updated successfully.',
                'data' => [
                    'merchant_id' => $merchant->id,
                    'name' => $merchant->name,
                    'status' => $merchant->status ? 'active' : 'inactive',
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the merchant status.',
            ], 500);
        }
    }
}
