<?php

namespace App\Models;

use App\Events\FeedSaved;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Overtrue\LaravelFollow\Traits\Followable;
use voku\helper\UTF8;

class Feed extends Model
{
    use HasFactory, Sluggable, Followable;

    protected $fillable = [
        'title',
        'url',
        'link',
        'copyright',
        'description',
        'author',
        'category_id',
        'author_id',
        'language',
        'image',
        'visible',
        'count',
        'generator',
        'last_episode'
    ];

    protected $appends = [
        'followed',
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

    public static function obtain(string $url)
    {

        $feed = \array_from_xml($url);

        if ($feed === false) {
            self::where('url', $url)->update(['not_update' => true]);
            return false;
        }

        if (empty($feed)) {
            throw new \Exception('Invalid feed');
        }

        $channel = $feed['rss']['channel'];

        $category = 0;
        if (isset($channel['itunes:category'])) {
            foreach ($channel['itunes:category'] as $category) {
                if (isset($category['@attributes']['text'])) {
                    $category_name = $category['@attributes']['text'];
                    $category = $category = Category::obtain($category_name);
                }
            }
        }

        $author = isset($channel['author']) ? (string)$channel['author'] : false;
        if ($author) {
            $author = Author::obtain($author);
        } elseif (isset($channel['author']) && is_array($channel['author']) && isset($channel['author']['@cdata'])) {
            $author = Author::obtain($author);
        }

        $description = '';

        if (isset($channel['description']) && is_string($channel['description'])) {
            $description = (string)$channel['description'];
        } elseif (isset($channel['description']) && is_array($channel['description']) && isset($channel['description']['@cdata'])) {
            $description = (string)$channel['description']['@cdata'];
        }

        $title = '';
        if (isset($channel['title']) && is_string($channel['title'])) {
            $title = (string)$channel['title'];
        } elseif (isset($channel['title']) && is_array($channel['title']) && isset($channel['title']['@cdata'])) {
            $title = (string)$channel['title']['@cdata'];
        }

        $copyright = '';
        if (isset($channel['copyright']) && is_string($channel['copyright'])) {
            $copyright = (string)$channel['copyright'];
        } elseif (isset($channel['copyright']) && is_array($channel['copyright']) && isset($channel['copyright']['@cdata'])) {
            $copyright = (string)$channel['copyright']['@cdata'];
        } elseif (isset($channel['copyright']) && is_string($channel['copyright'])) {
            $copyright = (string)$channel['copyright'];
        }

        $data = [
            'url' => $url,
            'title' => UTF8::fix_utf8($title),
            'language' => isset($channel['language']) ? (string)$channel['language'] : '',
            'copyright' => UTF8::fix_utf8($copyright),
            'description' => UTF8::fix_utf8($description),
            'image' => isset($channel['image']['url']) ?  (string)$channel['image']['url'] : '',
            'generator' => isset($channel['generator']) ? (string)$channel['generator'] : '',
            //'link' => isset($channel['image']['link']) ?  (string)$channel['image']['link'] : '',
            'link' => '',
            'visible' => true,
            'category_id' => $category->id ?? 0,
            'author_id' => $author->id ?? 0,
        ];

        $feed = Feed::updateOrCreate(
            ['url' => $url],
            $data
        );

        event(new FeedSaved($feed));

        return $feed;
    }

    public function get_new_episodes(): int
    {

        if ($this->updated_at->greaterThan(Carbon::now()->subMinutes(30))) {
            return 0;
        }

        $items = \array_from_xml($this->url);
        if ($items === false) {
            $this->not_update = true;
            $this->save();

            return false;
        }

        $channel = $items['rss']['channel'];

        if (!isset($channel['item'])) {
            return 0;
        }

        $count = count($channel['item']);

        /*if ($this->count == $count) {
            return 0;
        }*/

        foreach ($channel['item'] as $item) {

            $subtitle = false;
            if (isset($item['itunes:subtitle']) && isset($item['itunes:subtitle']['@cdata'])) {
                $subtitle =  $item['itunes:subtitle']['@cdata'];
            }

            $chapters = null;
            if (isset($item['podcast:chapters']) && !empty($item['podcast:chapters'])) {
                foreach ($item['podcast:chapters'] as $element) {
                    if ($element['@attributes']['type'] == 'text/vtt') {
                        $chapters = $element['@attributes']['url'];
                    }
                }
            }

            $description = '';
            if (isset($item['description'])) {
                $description = $item['description'];
            }
            if (is_array($description)) {
                $description = implode(' ', $description);
            }

            $title = '';
            if (isset($item['title'])) {
                $title = $item['title'];
            }
            if (is_array($title)) {
                $title = implode(' ', $title);
            }

            $data = [
                'feed_id' => $this->id,
                'title' => UTF8::fix_utf8($title),
                'subtitle' => UTF8::fix_utf8($subtitle == '0' ? '' : $subtitle),
                'description' => UTF8::fix_utf8(strip_tags((string)$description)),
                'published_at' => isset($item['pubDate']) ? (new Carbon((string)$item['pubDate']))->toDateTimeString() : null,
                'media_url' => isset($item['enclosure']['@attributes']['url']) ? (string)$item['enclosure']['@attributes']['url'] : '',
                'duration' => isset($item['enclosure']['@attributes']['length']) ? (int)$item['enclosure']['@attributes']['length'] : 0,
                'chapters' => $chapters
            ];

            if (!$data['media_url']) {
                continue;
            }

            $episode = Episode::updateOrCreate(
                [
                    'media_url' => $data['media_url'],
                    'feed_id' => $this->id
                ],
                $data
            );
        }

        //Obtenemos el episodio mas nuevo
        $last_episode = null;
        $feed = Episode::where('feed_id', $this->id)->orderBy('published_at', 'desc')->first();
        if ($feed) {
            $last_episode = $feed->published_at;
        }

        $this->update(
            [
                'count' => $count,
                'last_episode' => $last_episode
            ]
        );

        $this->touch();

        return $count;
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class)->orderBy('published_at', 'desc');
    }

    public function getFollowedAttribute()
    {
        return $this->isFollowedBy(auth()->user());
    }

    public function getImageAttribute()
    {
        if (!$this->attributes['image']) {
            return url('noimage.png');
        }

        return $this->attributes['image'];
    }
}
