<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    use HasFactory;

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