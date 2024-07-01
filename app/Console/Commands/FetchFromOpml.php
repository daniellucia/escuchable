<?php

namespace App\Console\Commands;

use App\Models\Feed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class FetchFromOpml extends Command
{

    protected $signature = 'fetch:opml {file}';

    protected $description = 'Obtiene los podcast desde un fichero OPML';

    public function handle()
    {
        $file = $this->argument('file');

        if (!Storage::exists($file)) {
            $this->error('File not exists');
            return;
        }

        $xml = array_from_opml($file);

        $feeds = $xml['body']['outline'];
        if (isset($xml['body']['outline']['outline'])) {
            $feeds = $xml['body']['outline']['outline'];
        }
        $count = count($feeds);

        if ($count == 0) {
            $this->error('No feed founds');
            return;
        }

        $this->info("{$count} feeds found");

        foreach ($feeds as $item) {
            $url = (string)$item['@attributes']['xmlUrl'];
            try {
                Feed::obtain($url);
                $this->info("Podcast feed fetched and stored successfully from {$url}");
            } catch (\Exception $e) {
                $this->error('Failed to fetch podcast feed: ' . $e->getMessage());
            }
            $this->info($url);
        }
    }
}
