<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_type_event');
    }
}
