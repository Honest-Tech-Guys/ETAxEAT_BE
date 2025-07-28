<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class Cuisine extends Model
{
    use Translatable;

    protected $translatable = ['name', 'about', 'description'];
    protected $guarded = [];

    protected $fillable = ['name', 'about', 'description', 'operating_hours', 'features', 'images', 'latitude', 'longitude', 'rating', 'is_active'];
    protected $casts = [
        'operating_hours' => 'array',
        'features' => 'array',
        'images' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating' => 'double',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_cuisine');
    }

    public function dishes()
    {
        return $this->belongsToMany(Dish::class, 'cuisine_dish');
    }
    public function cuisineTypes()
    {
        return $this->belongsToMany(CuisineType::class, 'cuisine_type_cuisine');
    }
    public function dishCategories()
    {
        return $this->belongsToMany(DishCategory::class, 'cuisine_dish_category');
    }
}
