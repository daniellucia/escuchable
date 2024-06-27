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
            Feed::obtain($url);
            $this->info('Podcast feed fetched and stored successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to fetch podcast feed: ' . $e->getMessage());
        }
    }
}
