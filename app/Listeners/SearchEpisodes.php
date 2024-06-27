<?php

namespace App\Listeners;

use App\Events\FeedSaved;
use App\Models\Episode;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SearchEpisodes
{
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
        Episode::obtain($event->feed);
    }
}
