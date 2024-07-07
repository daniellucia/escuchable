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

        $channel = $feed['channel'];
        $category = isset($channel['itunes:category']) ? (string)$channel['itunes:category']['@attributes']['text'] : false;
        if ($category) {
            $category = Category::obtain($category);
        }

        $author = isset($channel['author']) ? (string)$channel['author'] : false;
        if ($author) {
            $author = Author::obtain($author);
        }

        $data = [
            'url' => $url,
            'title' => UTF8::fix_utf8((string)$channel['title']),
            'language' => isset($channel['language']) ? (string)$channel['language'] : '',
            'copyright' => isset($channel['copyright']) ? (string)$channel['copyright'] : '',
            'description' => UTF8::fix_utf8(isset($channel['description']) ? (string)$channel['description'] : ''),
            'image' => isset($channel['image']['url']) ?  (string)$channel['image']['url'] : '',
            'generator' => isset($channel['generator']) ? (string)$channel['generator'] : '',
            'link' => isset($channel['image']['link']) ?  (string)$channel['image']['link'] : '',
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

        $items = \array_from_xml($this->url);
        if ($items === false) {
            $this->not_update = true;
            $this->save();

            return false;
        }

        $channel = $items['channel'];

        if (!isset($channel['item'])) {
            return 0;
        }

        $count = count($channel['item']);

        /*if ($this->count == $count) {
            return 0;
        }*/

        if (isset($channel['title'])) {
            //$channel['item'] = [];
            //$channel['item'][] = $channel;
        }

        foreach ($channel['item'] as $item) {

            $subtitle = isset($item['itunes:subtitle']) ? (string)$item['itunes:subtitle'] : false;

            $data = [
                'feed_id' => $this->id,
                'title' => UTF8::fix_utf8((string)$item['title']),
                'subtitle' => UTF8::fix_utf8($subtitle == '0' ? '' : $subtitle),
                'description' => isset($item['description']) ? UTF8::fix_utf8(strip_tags((string)$item['description'])) : '',
                'published_at' => isset($item['pubDate']) ? (new Carbon((string)$item['pubDate']))->toDateTimeString() : null,
                'media_url' => isset($item['enclosure']['@attributes']['url']) ? (string)$item['enclosure']['@attributes']['url'] : '',
                'duration' => (int)isset($item['enclosure']['@attributes']['length']) ? (string)$item['enclosure']['@attributes']['length'] : 0,
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
