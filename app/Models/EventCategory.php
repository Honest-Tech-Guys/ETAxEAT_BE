<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class EventCategory extends Model
{
    use HasFactory , Translatable;
    protected $translatable = ['name', 'description'];
    protected $guarded = [];

    protected $fillable = ['name', 'description', 'is_active'];
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_category_event');
    }
}
