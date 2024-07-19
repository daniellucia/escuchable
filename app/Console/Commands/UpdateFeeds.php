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
    protected $signature = 'update:episodes {limit?}';

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
        $limit = $this->argument('limit') ?? 50;

        $start = microtime(true);
        $feeds = Feed::where('updated_at', '<', Carbon::now()->subHours(4)->toDateTimeString())->where('not_update', false)->limit($limit)->get();

        foreach ($feeds as $feed) {
            $this->comment($feed->title);
            event(new FeedSaved($feed));
        }

        $end = microtime(true);
        $time = $end - $start;
        $this->comment('');
        $this->comment(date("H:i:s", $time));
    }
}
