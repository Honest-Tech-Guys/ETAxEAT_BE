<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class Category extends Model
{
    use Translatable;
    protected $translatable = ['name', 'description'];
    protected $fillable = ['name', 'description', 'is_active'];
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
