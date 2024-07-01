<?php

namespace App\Models;

use App\Events\FeedSaved;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Overtrue\LaravelFollow\Traits\Followable;


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

        $category = isset($feed['rss']['channel']['itunes:category']) ? (string)$feed['rss']['channel']['itunes:category']['@attributes']['text'] : false;
        if ($category) {
            $category = Category::obtain($category);
        }

        $author = isset($feed['rss']['channel']['author']) ? (string)$feed['rss']['channel']['author'] : false;
        if ($author) {
            $author = Author::obtain($author);
        }

        $data = [
            'url' => $url,
            'title' => (string)$feed['rss']['channel']['title'],
            'language' => isset($feed['rss']['channel']['language']) ? (string)$feed['rss']['channel']['language'] : '',
            'copyright' => isset($feed['rss']['channel']['copyright']) ? (string)$feed['rss']['channel']['copyright'] : '',
            'description' => isset($feed['rss']['channel']['description']) ? (string)$feed['rss']['channel']['description'] : '',
            'image' => isset($feed['rss']['channel']['image']['url']) ?  (string)$feed['rss']['channel']['image']['url'] : '',
            'generator' => isset($feed['rss']['channel']['generator']) ? (string)$feed['rss']['channel']['generator'] : '',
            'link' => isset($feed['rss']['channel']['image']['link']) ?  (string)$feed['rss']['channel']['image']['link'] : '',
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

        if (!isset($items['rss']['channel']['item'])) {
            return 0;
        }

        $count = count($items['rss']['channel']['item']);
        if ($this->count == $count) {
            return 0;
        }

        foreach ($items['rss']['channel']['item'] as $item) {

            $subtitle = isset($item['itunes:subtitle']) ? (string)$item['itunes:subtitle'] : false;

            $data = [
                'feed_id' => $this->id,
                'title' => (string)$item['title'],
                'subtitle' => $subtitle,
                'description' => isset($item['description']) ? (string)$item['description'] : '',
                'publication_date' => isset($item['pubDate']) ? (new Carbon((string)$item['pubDate']))->toDateTimeString() : null,
                'media_url' => isset($item['enclosure']['@attributes']['url']) ? (string)$item['enclosure']['@attributes']['url'] : '',
                'duration' => isset($item['enclosure']['@attributes']['length']) ? (string)$item['enclosure']['@attributes']['length'] : 0,
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
        $feed = Episode::where('feed_id', $this->id)->orderBy('publication_date', 'desc')->first();
        if ($feed) {
            $last_episode = $feed->publication_date;
        }

        $this->update(
            [
                'count' => $count,
                'last_episode' => $last_episode
            ]
        );

        return $count;
    }
}
