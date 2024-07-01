<?php

namespace App\Models;

use App\Events\FeedSaved;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'count',
    ];

    public static function obtain(string $url)
    {

        $xml = simplexml_load_file($url, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $feed = json_decode($json, true);

        $data = [
            'url' => $url,
            'title' => (string)$feed['channel']['title'],
            'description' => $feed['channel']['description'] ? (string)$feed['channel']['description'] : '',
            'image' => isset($feed['channel']['url']) ?  (string)$feed['channel']['image']['url'] : '',
            'generator' => isset($feed['channel']['generator']) ? (string)$feed['channel']['generator'] : '',
            'link' => $feed['channel']['link'] ?  (string)$feed['channel']['link'] : '',
            'visible' => true,
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

        $xml = simplexml_load_file($this->url, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $items = json_decode($json, true);

        if (!isset($items['channel']['item'])) {
            return 0;
        }

        $count = count($items['channel']['item']);
        if ($this->count == $count) {
            return 0;
        }

        foreach ($items['channel']['item'] as $item) {

            $data = [
                'feed_id' => $this->id,
                'title' => (string)$item['title'],
                'description' => isset($item['description']) ? (string)$item['description'] : '',
                'publication_date' => isset($item['pubDate']) ? (new Carbon((string)$item['pubDate']))->toDateTimeString() : null,
                'media_url' => isset($item['enclosure']['@attributes']['url']) ? (string)$item['enclosure']['@attributes']['url'] : '',
                'duration' => isset($item['enclosure']['@attributes']['length']) ? (string)$item['enclosure']['@attributes']['length'] : 0,
            ];

            if (!$data['media_url']) {
                continue;
            }

            Episode::updateOrCreate(
                [
                    'media_url' => $data['media_url'],
                    'feed_id' => $this->id
                ],
                $data
            );
        }

        $this->update(['count' => $count]);

        return $count;
    }
}
