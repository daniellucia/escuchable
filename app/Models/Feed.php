<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'url',
        'link',
        'description',
        'author',
        'category_id',
        'language',
        'image',
        'visible',
    ];
}
