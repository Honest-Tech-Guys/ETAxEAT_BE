<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class RechargingCategory extends Model
{
    use HasFactory , Translatable;
    protected $guarded = [];

    public function rechargingStations()
    {
        return $this->belongsToMany(RechargingStation::class, 'recharging_category_station');
    }
}
