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
        'published_at'
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
}
