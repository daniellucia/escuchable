<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Episode;

class PlayedController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

        $user = $request->user();

        $episode_id = $request->episode_id;
        $finished = boolval($request->finished);
        $time = intval($request->time);

        if (!$episode_id || $episode_id == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Episode ID is required'
            ]);
        }

        $episode = Episode::find($episode_id);
        if (!$episode) {
            return response()->json([
                'success' => false,
                'message' => 'Episode not found'
            ]);
        }

        return response()->json([
            'success' => $user->played($episode, $time,  $finished)
        ]);
    }
}
