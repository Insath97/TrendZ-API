<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'saloon_id',
        'code',
        'name',
        'icon',
        'description',
        'price',
        'duration',
        'status',
        'delete_status'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'saloon_id');
    }
}
