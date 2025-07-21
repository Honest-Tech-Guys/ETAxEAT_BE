<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class Event extends Model
{
    use HasFactory , Translatable;

    protected $guarded = [];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'images' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function eventCategories()
    {
        return $this->belongsToMany(EventCategory::class, 'event_category_event');
    }

    public function eventTypes()
    {
        return $this->belongsToMany(EventType::class, 'event_type_event');
    }
}
