<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class Cuisine extends Model
{
    use Translatable;

    protected $translatable = ['name', 'about', 'description'];
    protected $guarded = [];

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
}
