<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class CuisineType extends Model
{
    use Translatable;
    protected $translatable = ['name', 'description'];
    protected $guarded = [];

    protected $fillable = ['name', 'description', 'is_active'];
    public function cuisines()
    {
        return $this->belongsToMany(Cuisine::class, 'cuisine_type_cuisine');
    }
}
