<?php

namespace App\Models;

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
    ];

    public static function obtain(string $url)
    {

        $xml = simplexml_load_file($url, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $feed = json_decode($json, true);

        $data = [
            'url' => $url,
            'title' => (string)$feed['channel']['title'],
            'description' => (string)$feed['channel']['description'],
            'image' => (string)$feed['channel']['image']['url'],
            'generator' => (string)$feed['channel']['generator'],
            'link' => (string)$feed['channel']['link'],
            'visible' => true,
        ];

        $feed = Feed::updateOrCreate(
            ['url' => $url],
            $data
        );

        return $feed;
    }
}
