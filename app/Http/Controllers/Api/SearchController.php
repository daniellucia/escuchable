<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Feed;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'search'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $feeds = Feed::query()
            ->when(request('search'), function ($query, $search) {
                $query->whereFullText(['title', 'description'], $search);
            }, function ($query) {
                $query->latest();
            })->get();

        return response()->json([
            'success' => true,
            'feeds' => $feeds
        ]);
    }
}
