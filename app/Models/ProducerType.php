<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class ProducerType extends Model
{
    use HasFactory , Translatable;
    protected $translatable = ['name', 'description'];
    protected $guarded = [];

    protected $fillable = ['name', 'description', 'is_active'];
    public function natureProduces()
    {
        return $this->belongsToMany(NatureProduce::class, 'nature_produce_producer_type');
    }
}
