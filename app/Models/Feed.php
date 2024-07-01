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
            'title' => (string)$channel['title'],
            'language' => isset($channel['language']) ? (string)$channel['language'] : '',
            'copyright' => isset($channel['copyright']) ? (string)$channel['copyright'] : '',
            'description' => isset($channel['description']) ? (string)$channel['description'] : '',
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
        $channel = $items['channel'];

        if (!isset($channel['item'])) {
            return 0;
        }

        $count = count($channel['item']);
        if ($this->count == $count) {
            return 0;
        }

        foreach ($channel['item'] as $item) {

            $subtitle = isset($item['itunes:subtitle']) ? (string)$item['itunes:subtitle'] : false;

            $data = [
                'feed_id' => $this->id,
                'title' => (string)$item['title'],
                'subtitle' => $subtitle == '0' ? '' : $subtitle,
                'description' => strip_tags(isset($item['description']) ? (string)$item['description'] : ''),
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
