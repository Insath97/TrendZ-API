<?php

namespace App\Observers;

use App\Models\BookingSlots;
use App\Models\Slot;

class BookingSlotObserver
{
    public function created(BookingSlots $bookingSlot)
    {
        $slot = Slot::find($bookingSlot->slot_id);

        if ($slot) {

            $currentBookings = BookingSlots::where('slot_id', $slot->id)
                ->whereDate('created_at', now()->toDateString())
                ->count();

            if ($currentBookings >= $slot->max_bookings) {
                $slot->update([
                    'is_recurring' => false,
                ]);
            }
        }
    }
}
