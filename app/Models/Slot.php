<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    use HasFactory;

    protected $fillable = [
        'saloon_id',
        'slot_name',
        'start_time',
        'end_time',
        'max_bookings',
        'is_recurring',
        'is_active',
        'delete_status',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'saloon_id');
    }

    
}
