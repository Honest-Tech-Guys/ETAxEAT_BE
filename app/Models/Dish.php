<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class Dish extends Model
{
    use HasFactory , Translatable;

    protected $guarded = [];

    // Updated relationship
    public function cuisines()
    {
        return $this->belongsToMany(Cuisine::class, 'cuisine_dish');
    }
    public function foodTrucks()
    {
        return $this->belongsToMany(FoodTruck::class, 'dish_food_truck');
    }
}