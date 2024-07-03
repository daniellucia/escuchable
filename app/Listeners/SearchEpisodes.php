<?php

namespace App\Listeners;

use App\Events\FeedSaved;
use App\Models\Episode;
use App\Models\Feed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SearchEpisodes
{
    public string $connection = 'database';
    public string $queue = 'listeners';

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FeedSaved $event): void
    {
        $event->feed->get_new_episodes();
    }
}
