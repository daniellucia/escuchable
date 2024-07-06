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
        $start = microtime(true);
        $feeds = Feed::where('updated_at', '<', Carbon::now()->subHours(6)->toDateTimeString())->limit(100)->get();
        $bar = $this->output->createProgressBar(count($feeds));
        $bar->start();
        foreach ($feeds as $feed) {
            $feed->touch();
            event(new FeedSaved($feed));
            $bar->advance();
        }
        $bar->finish();
        $end = microtime(true);
        $time = $end - $start;
        $this->comment('');
        $this->comment(date("H:i:s", $time));
    }
}
