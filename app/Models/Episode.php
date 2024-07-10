<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Overtrue\LaravelFavorite\Traits\Favoriteable;

class Episode extends Model
{
    use HasFactory, Sluggable, Favoriteable;

    protected $fillable = [
        'feed_id',
        'title',
        'link',
        'description',
        'subtitle',
        'image',
        'media_url',
        'duration',
        'published_at',
        'chapters'
    ];

    protected $appends = [
        'played',
    ];

    /**
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function feed()
    {
        return $this->belongsTo(Feed::class);
    }

    public function getImageAttribute()
    {
        if (!is_null($this->attributes['image'])) {
            return $this->attributes['image'];
        } else {
            return $this->feed->image;
        }
    }

    public function getPlayedAttribute()
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        $played = EpisodePlayed::where('user_id', $user->id)
            ->where('episode_id', $this->id)
            ->first();

        return $played ? true : false;
    }
}
