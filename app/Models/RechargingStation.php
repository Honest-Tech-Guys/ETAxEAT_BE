<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RechargingStation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'operating_hours' => 'array',
        'features' => 'array',
        'images' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating' => 'double',
    ];

    public function rechargingCategories()
    {
        return $this->belongsToMany(RechargingCategory::class, 'recharging_category_station');
    }

    public function chargingTypes()
    {
        return $this->belongsToMany(ChargingType::class, 'charging_type_station');
    }

    public function vehicleTypes()
    {
        return $this->belongsToMany(VehicleType::class, 'vehicle_type_station');
    }

    public function chargingPowers()
    {
        return $this->belongsToMany(ChargingPower::class, 'charging_power_station');
    }
}
