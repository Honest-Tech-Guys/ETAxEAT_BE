<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodTruck extends Model
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

    /**
     * The categories that belong to the food truck.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_food_truck');
    }

    /**
     * The dishes that are served by the food truck.
     */
    public function dishes()
    {
        return $this->belongsToMany(Dish::class, 'dish_food_truck');
    }
}