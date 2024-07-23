<?php

namespace App\Console\Commands;

use App\Models\Feed;
use App\Models\Recommend;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class GetRecommendations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:recommendations {user?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get recommendations for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = [];
        $user_id = $this->argument('user') ?? 4;
        $user = User::find($user_id);

        if (!$user) {
            return 'User not found';
        }

        //Obtenemos los podcasts a los que sigue
        $followed = $user->followed();
        if (empty($followed)) {
            return 'User does not follow any podcast';
        }

        $response[] = "Tengo una app de podcast, donde los usuarios puedes seguir a los que más le gusten.";
        $response[] = "Voy a pasarte una lista de podcast que sigue un usuario. Tendrá el siguiente formato: {id} | {title} | {description}";
        $response[] = "Luego, te daré una lista de podcast que podrían gustarle a ese usuario y debes elegir algunos en base a su descripción.";
        $response[] = "Esta es la lista de los podcasts a los que sigue:";
        foreach ($followed as $podcast) {
            $description = preg_replace("/\r|\n/", "",  strip_tags($podcast->description));
            $response[] = "{$podcast->id} | {$podcast->title} | {$description}";
        }

        $response[] = " ";
        $response[] = "Esta es la lista de los podcasts que podria seguir:";

        $feeds = Feed::whereNotIn('id', $followed->pluck('id'))->where('count', '>', 10)->orderBy('last_episode', 'desc')->limit(30)->get();
        foreach ($feeds as $podcast) {
            $description = preg_replace("/\r|\n/", "",  strip_tags($podcast->description));
            $response[] = "{$podcast->id} | {$podcast->title} | {$description}";
        }

        $response[] = " ";
        $response[] = "¿cual le recomendarias?";
        $response[] = "Retorna un json objeto json con dos propiedas. La primera 'IDS', que contendrá los ids de los podcasts recomendados y la segunda 'MESSAGE' que contendrá un texto con el motivo de porqué has elegido esos podcasts.";
        $response[] = "En el mensaje, usa un lenguaje coloquial y amistoso.";


        $message = implode(PHP_EOL, $response);

        $repsonse = Cache::remember('openai.' . md5($message), 20, function () use ($message, $user_id) {
            try {
                $response = $this->sendToOpenAI($message);
                if (empty($response)) {
                    return 'No response from OpenAI';
                }

                if (!$this->isJson($response)) {
                    return 'Invalid JSON response from OpenAI';
                }

                $response = json_decode($response, true);
                $ids = array_map('intval', $response['IDS']);

                Recommend::where('user_id', $user_id)->delete();

                foreach ($ids as $id) {
                    Recommend::create([
                        'user_id' => $user_id,
                        'feed_id' => $id,
                    ]);
                }

                return $response;
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });

        dump($repsonse);

        return 1;
    }

    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private function sendToOpenAI($message)
    {
        $client = new Client();
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ];
        $body = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [['role' => 'user', 'content' => $message]],
        ];
        $response = $client->post($url, [
            'headers' => $headers,
            'json' => $body,
        ]);
        $result = json_decode($response->getBody()->getContents(), true);

        return $result['choices'][0]['message']['content'];
    }
}
