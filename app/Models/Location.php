<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius'
    ];

    // ✅ aktifkan timestamps (default sebenarnya true)
    public $timestamps = true;
}