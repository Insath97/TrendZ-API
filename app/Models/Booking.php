<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'shop_id',
        'booking_date',
        'unique_reference',
        'booking_number',
        'total_amount',
        'status',
        'cancellation_reason'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'booking_services');
    }

    public function slots()
    {
        return $this->belongsToMany(Slot::class, 'booking_slots');
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barber_id');
    }

    public function cancel($reason)
    {
        $this->update([
            'status' => 'canceled',
            'cancellation_reason' => $reason,
        ]);
    }

    public function reschedule($newDate, $newSlotId)
    {
        $this->update([
            'booking_date' => $newDate,
            'status' => 'rescheduled',
        ]);

        $this->slots()->sync([$newSlotId]);
    }
}
