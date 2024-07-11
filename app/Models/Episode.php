<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Overtrue\LaravelFavorite\Traits\Favoriteable;
use Illuminate\Support\Facades\Storage;
use Podlove\Webvtt\Parser;
use Podlove\Webvtt\ParserException;

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

    public function getChaptersAttribute()
    {
        if (!is_null($this->attributes['chapters'])) {

            $filename = "episodes/{$this->id}.vtt";

            if (Storage::exists($filename)) {
                $content = Storage::get($filename);
            } else {
                $content = file_get_contents($this->attributes['chapters']);
                if ($content) {
                    Storage::put($filename, $content);
                }
            }

            return $content;

            $parser = new Parser();
            $result = $parser->parse(str_replace('//', '/', preg_replace("/\n/m", '\n', $content)));
            $chapters = [];
            foreach($result['cues'] as $chapter) {
                $chapters[] = [
                    'startTime' => $chapter->start,
                    'endTime' => $chapter->end,
                    'title' => $chapter->title,
                ];
            }
            return $chapters;
        }

        return $this->attributes['chapters'];
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
