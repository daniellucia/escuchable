<?php

namespace App\Console\Commands;

use App\Models\Feed;
use App\Models\Recommend;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;

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
            $this->error('User not found');
            return;
        }

        //Obtenemos los podcasts a los que sigue
        $followed = $user->followed();
        if (empty($followed)) {
            $this->error('User does not follow any podcast');
            return;
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
        $response[] = "¿cual le recomendarias? Dame solo el listado de los id separados por comas";


        $message = implode(PHP_EOL, $response);

        try {
            $response = $this->sendToOpenAI($message);
            if (empty($response)) {
                $this->error('No response from OpenAI');
                return;
            }

            $response = explode(',', $response);
            $response = array_map('intval', $response);

            Recommend::where('user_id', $user_id)->delete();

            foreach($response as $id) {
                Recommend::create([
                    'user_id' => $user_id,
                    'feed_id' => $id,
                ]);
            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        return 1;
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
