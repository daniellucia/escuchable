<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Episode;

class PlaylistController extends Controller
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

        if ($request->isMethod('get')) {
            return response()->json([
                'success' => true,
                'playlist' => $user->playlist()
            ]);
        }

        $episode_id = $request->episode_id;
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

        if(!$user->hasInPlaylist($episode)) {
            $user->addToPlaylist($episode);
        } else {
            $user->removeFromPlaylist($episode);
        }

        return response()->json([
            'success' => true,
            'in_playlist' => $user->hasInPlaylist($episode),
            'playlist' => $user->playlist()
        ]);
    }
}
