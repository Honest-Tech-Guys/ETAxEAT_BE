<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class Announcement extends Model
{
    use HasFactory , Translatable;

    protected $translatable = ['title', 'content'];
    protected $guarded = [];

    protected $fillable = ['title', 'content', 'publish_at', 'images', 'is_active'];
    protected $casts = [
        'publish_at' => 'datetime',
        'images' => 'array',
    ];
}
