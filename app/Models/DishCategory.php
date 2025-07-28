<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class DishCategory extends Model
{
    use Translatable;
    protected $translatable = ['name', 'description'];
    protected $guarded = [];

    public function cuisines()
    {
        return $this->belongsToMany(Cuisine::class, 'cuisine_dish_category');
    }
}
