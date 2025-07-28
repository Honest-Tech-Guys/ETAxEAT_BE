<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class VehicleType extends Model
{
    use HasFactory , Translatable;
    protected $guarded = [];

    protected $fillable = ['name', 'description', 'is_active'];
    protected $translatable = ['name', 'description'];
    public function rechargingStations()
    {
        return $this->belongsToMany(RechargingStation::class, 'vehicle_type_station');
    }
}
