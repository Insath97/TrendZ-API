<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalkingCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'fullname',
        'age',
        'phone_number',
        'gender',
        'address',
        'email',
        'occupation'
    ];

    // Relationship with services
    public function services()
    {
        return $this->belongsToMany(Service::class, 'customer_service');
    }
}
