<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargingPower extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function rechargingStations()
    {
        return $this->belongsToMany(RechargingStation::class, 'charging_power_station');
    }
}
