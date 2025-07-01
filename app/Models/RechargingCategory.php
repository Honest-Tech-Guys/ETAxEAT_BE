<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RechargingCategory extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function rechargingStations()
    {
        return $this->belongsToMany(RechargingStation::class, 'recharging_category_station');
    }
}
