<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PgSql\Lob;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'code',
        'name',
        'address',
        'phone_number',
        'email',
        'status',
        'delete_status'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function merchant()
    {
        return $this->hasOne(Merchant::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'saloon_id');
    }

    public function slots()
    {
        return $this->hasMany(Slot::class, 'saloon_id');
    }
}
