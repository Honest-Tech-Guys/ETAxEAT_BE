<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class EventType extends Model
{
    use HasFactory , Translatable;
    protected $guarded = [];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_type_event');
    }
}
