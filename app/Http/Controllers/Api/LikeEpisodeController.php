<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Episode;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class LikeEpisodeController extends Controller
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

        $user->toggleFavorite($episode);

        return response()->json([
            'success' => true,
            'following' => $user->hasFavorited($episode)
        ]);
    }
}
