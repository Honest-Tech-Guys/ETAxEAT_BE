<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class NatureProduce extends Model
{
    use HasFactory , Translatable;

    protected $guarded = [];

    protected $casts = [
        'operating_hours' => 'array',
        'images' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating' => 'double',
    ];

    public function producerTypes()
    {
        return $this->belongsToMany(ProducerType::class, 'nature_produce_producer_type');
    }

    public function productTypes()
    {
        return $this->belongsToMany(ProductType::class, 'nature_produce_product_type');
    }
}
