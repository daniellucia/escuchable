<?php

namespace App\Console\Commands;

use App\Models\Feed;
use App\Models\Url;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class CrawlerUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:url';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawler Url';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = new Client();
        while (true) {
            $url = Url::where('read', false)->orderBy('priority', 'desc')->first();

            if ($url) {
                $start = microtime(true);
                $feeds = [];
                $urls = [];
                $url->update(['read' => true]);

                $continue = false;
                foreach (explode(',', getenv('SKIP_URL_STOP_WORDS')) as $skip) {
                    if (strpos($url->url, $skip) !== false) {
                        $this->info('[Skip] ' . $url->url . ' | ' . $url->priority . ' | ' . date("H:i:s"));
                        $continue = true;
                    }
                }

                if ($continue) {
                    continue;
                }

                $message = '[Finish] ' . $url->url . ' | ' . $url->priority . ' | ' . date("H:i:s");

                try {
                    $response = $client->get($url->url);
                    $content = $response->getBody()->getContents();
                    $crawler = new Crawler($content);

                    $data = $crawler->filter('a')->each(function (Crawler $node, $i) use ($url) {

                        $href = $node->attr('href');
                        if (substr($href, 0, 2) == '//') {
                            $href = 'https:' . $href;
                        }

                        if (substr($href, 0, 1) == '/') {
                            $href = null;
                        }

                        if ($this->isSameDomain($url->url, $href)) {
                            return $href;
                        }
                    });

                    $urls = array_filter($data, function ($value) {
                        return !is_null($value) && $value !== '' && $value !== '#' && $value !== 'javascript:void(0);';
                    });

                    foreach ($urls as $url) {
                        $data = ['priority' => 0, 'url' => $url];
                        if (strpos($url, '_sq_') !== false) {
                            $data = ['priority' => 5, 'url' => $url];
                        }
                        Url::firstOrCreate(['url' => $url], $data);
                    }

                    $data = $crawler->filter('a')->each(function (Crawler $node, $i) use ($url) {
                        return $node->attr('href');
                    });

                    $feeds = array_filter($data, function ($value) {
                        return !is_null($value) && $this->isFeedUrl($value);
                    });
                } catch (\Exception $e) {
                    $this->error('[FINISH] Failed to fetch URL: ' . $e->getMessage());
                    continue;
                }

                if (!empty($feeds)) {
                    foreach ($feeds as $feed) {
                        try {
                            Feed::obtain(trim($feed));
                            $this->info("[Feed] Podcast feed fetched and stored successfully from {$feed}");
                        } catch (\Exception $e) {
                            $this->error('[Error]Failed to fetch podcast feed: ' . $e->getMessage());
                        }
                    }
                }

                sleep(1);
                $end = microtime(true);
                $time = $end - $start;
                $this->comment($message . ' | ' . date("H:i:s", $time));
            }
        }
    }

    private function isFeedUrl(string $url): bool
    {
        return !is_null($url) && (substr($url, -4) == '.xml' || substr($url, -4) == '.rss' || substr($url, -6) == '/feed/' || substr($url, -4) == '/rss');
    }

    private function isSameDomain($parentUrl, $urlToVerify): bool
    {

        if (strlen($urlToVerify) < 5) {
            return false;
        }

        if ($urlToVerify == null || $urlToVerify == '') {
            return false;
        }

        if (substr($urlToVerify, 0, 1) == '/') {
            $url1 = parse_url($parentUrl);
            $urlToVerify = $url1['scheme'] . '://' . $url1['host'] . $urlToVerify;
        }

        if (!filter_var($urlToVerify, FILTER_VALIDATE_URL)) {
            return false;
        }

        $url1 = parse_url($parentUrl);
        $url2 = parse_url($urlToVerify);

        if (!isset($url2['host'])) {
            return false;
        }

        if (isset($url2['query'])) {
            return false;
        }

        if ($url1['host'] != $url2['host']) {
            return false;
        }

        return true;
    }
}
