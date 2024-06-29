<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;

class Episode extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'feed_id',
        'title',
        'link',
        'description',
        'image',
        'media_url',
        'duration',
        'publication_date'
    ];

    /**
     * @return array
     */
    public function sluggable(): array {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public static function obtain(Feed $feed)
    {

        $xml = simplexml_load_file($feed->url, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $items = json_decode($json, true);

        if (!isset($items['channel']['item'])) {
            return;
        }

        $count = count($items['channel']['item']);
        if ($feed->count == $count) {
            return;
        }

        foreach ($items['channel']['item'] as $item) {

            $data = [
                'feed_id' => $feed->id,
                'title' => (string)$item['title'],
                'description' => isset($item['description']) ? (string)$item['description'] : '',
                'publication_date' => isset($item['pubDate']) ? (new Carbon((string)$item['pubDate']))->toDateTimeString() : null,
                'media_url' => isset($item['enclosure']['@attributes']['url']) ? (string)$item['enclosure']['@attributes']['url'] : '',
                'duration' => isset($item['enclosure']['@attributes']['length']) ? (string)$item['enclosure']['@attributes']['length'] : 0,
            ];

            if (!$data['media_url']) {
                continue;
            }

            self::updateOrCreate(
                [
                    'media_url' => $data['media_url'],
                    'feed_id' => $feed->id
                ],
                $data
            );
        }

        $feed->update(['count' => $count]);
    }
}
