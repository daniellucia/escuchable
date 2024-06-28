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
        }

        $xml = simplexml_load_string(Storage::get($file));

        foreach ($xml->body->outline as $item) {
            $url = (string)$item['xmlUrl'];
            Artisan::call("fetch:feed {$url}");
            $this->info($url);
        }
    }
}
