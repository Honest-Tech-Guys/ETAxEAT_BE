<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class Announcement extends Model
{
    use HasFactory , Translatable;

    protected $guarded = [];

    protected $casts = [
        'publish_at' => 'datetime',
        'images' => 'array',
    ];
}
