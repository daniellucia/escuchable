<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Feed;

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
        $feed_id = $request->feed_id;
        if (!$feed_id || $feed_id == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Feed ID is required'
            ]);
        }

        $feed = Feed::find($feed_id);
        if (!$feed) {
            return response()->json([
                'success' => false,
                'message' => 'Feed not found'
            ]);
        }

        if(!$user->hasInPlaylist($feed)) {
            $user->addToPlaylist($feed);
        } else {
            $user->removeFromPlaylist($feed);
        }

        return response()->json([
            'success' => true,
            'in_playlist' => $user->hasInPlaylist($feed),
            'playlist' => $user->playlist
        ]);
    }
}
