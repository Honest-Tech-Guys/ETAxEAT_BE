<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProducerType extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function natureProduces()
    {
        return $this->belongsToMany(NatureProduce::class, 'nature_produce_producer_type');
    }
}
