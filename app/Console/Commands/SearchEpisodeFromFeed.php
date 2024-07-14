<?php

namespace App\Console\Commands;

use App\Models\Feed;
use Illuminate\Console\Command;

class SearchEpisodeFromFeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:episodes {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search episodes from feed url';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');
        //try {

            $feed = Feed::where('url', $url)->first();
            if (!$feed) {
                $feed = Feed::obtain($url);
            }

            if ($feed) {
                $count = $feed->get_new_episodes();
                $this->info("{$count} found");
            }

        //} catch (\Exception $e) {
        //    $this->error('Failed to fetch podcast feed: ' . $e->getMessage());
        //}
    }
}
