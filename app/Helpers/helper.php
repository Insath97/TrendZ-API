<?php

use App\Models\Slot;

function validateSlot(string $id)
{

    $slot = Slot::find($id);

    if (!$slot) {
        return response()->json([
            'success' => false,
            'message' => 'Slot not found',
        ], 404);
    }

    /* calc */
}
