<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'delete_status',
    ];

    public function shop()
    {
        return $this->hasOne(Shop::class, 'location_id');
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'location_id');
    }
}
