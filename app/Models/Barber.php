<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    use HasFactory;

    protected $fillable = [
        'saloon_id',
        'name',
        'code',
        'phone',
        'email',
        'image',
        'description',
        'is_active',
        'is_available'
    ];

    public function shops()
    {
        return $this->belongsTo(Shop::class, 'saloon_id');
    }
}
