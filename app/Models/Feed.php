<?php

namespace App\Models;

use App\Events\FeedSaved;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Feed extends Model
{
    use HasFactory, Sluggable;

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
        'generator',
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

    public static function obtain(string $url)
    {

        $xml = simplexml_load_file($url, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $feed = json_decode($json, true);

        $category = isset($feed['channel']['itunes:category']) ? (string)$feed['channel']['itunes:category'] : false;
        if ($category) {
            $category = Category::obtain($category);
        }

        $data = [
            'url' => $url,
            'title' => (string)$feed['channel']['title'],
            'language' => isset($feed['channel']['language']) ? (string)$feed['channel']['language'] : '',
            'description' => isset($feed['channel']['description']) ? (string)$feed['channel']['description'] : '',
            'image' => isset($feed['channel']['image']['url']) ?  (string)$feed['channel']['image']['url'] : '',
            'generator' => isset($feed['channel']['generator']) ? (string)$feed['channel']['generator'] : '',
            'link' => isset($feed['channel']['image']['link']) ?  (string)$feed['channel']['image']['link'] : '',
            'visible' => true,
            //'category_id' => $category->id ?? 0,
        ];

        $feed = Feed::updateOrCreate(
            ['url' => $url],
            $data
        );

        event(new FeedSaved($feed));

        return $feed;
    }
}
