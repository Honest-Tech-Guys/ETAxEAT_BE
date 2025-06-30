<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    public function cuisines()
    {
        return $this->belongsToMany(Cuisine::class, 'category_cuisine');
    }
    public function foodTrucks()
    {
        return $this->belongsToMany(FoodTruck::class, 'category_food_truck');
    }
}
