<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EpisodePlayed extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'episode_id',
        'time',
        'finished'
    ];
}
