<?php

namespace App\Console\Commands;

use App\Models\Feed;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Events\FeedSaved;

class UpdateFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:episodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update episodes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $feeds = Feed::where('updated_at', '<', Carbon::now()->subHours(5)->toDateTimeString())->get();
        foreach ($feeds as $feed) {
            event(new FeedSaved($feed));
        }
    }
}
