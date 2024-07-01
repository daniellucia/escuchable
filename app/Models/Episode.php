<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'feed_id',
        'title',
        'link',
        'description',
        'image',
        'media_url',
        'duration',
        'publication_date'
    ];
}
