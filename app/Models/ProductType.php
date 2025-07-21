<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function natureProduces()
    {
        return $this->belongsToMany(NatureProduce::class, 'nature_produce_product_type');
    }
}
