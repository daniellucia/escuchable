<?php

namespace App\Console\Commands;

use App\Models\Feed;
use Illuminate\Console\Command;

class FetchPodcastFeed extends Command
{

    protected $signature = 'fetch:feed {url}';
    protected $description = 'Obtención de podcasts y almacenamiento de datos en la base de datos';

    public function handle()
    {
        $url = $this->argument('url');

        try {
            $xml = simplexml_load_file($url, "SimpleXMLElement", LIBXML_NOCDATA);
            $json = json_encode($xml);
            $feed = json_decode($json, true);


            Feed::updateOrCreate(
                ['uid' => md5($url)],
                [
                    'uid' => md5($url),
                    'url' => $url,
                    'title' => (string)$feed['channel']['title'],
                    'description' => (string)$feed['channel']['description'],
                    'image' => (string)$feed['channel']['image']['url'],
                    'generator' => (string)$feed['channel']['generator'],
                    'link' => (string)$feed['channel']['link'],
                ]
            );

            $this->info('Podcast feed fetched and stored successfully.');

        } catch (\Exception $e) {

            $this->error('Failed to fetch podcast feed: ' . $e->getMessage());

        }
    }
}
