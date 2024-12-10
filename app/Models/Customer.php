<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'image',
        'name',
        'email',
        'password',
        'gender',
        'dob',
        'phone_number'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->dob)->age;
    }
}
