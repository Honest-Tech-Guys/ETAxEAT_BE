<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class FoodTruckType extends Model
{
    use Translatable;
    protected $translatable = ['name', 'description'];
    protected $guarded = [];

    public function FoodTrucks()
    {
        return $this->belongsToMany(FoodTruck::class, 'foodtruck_type_foodtruck');
    }
}
