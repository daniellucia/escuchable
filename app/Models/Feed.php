<?php

namespace App\Models;

use App\Events\FeedSaved;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
