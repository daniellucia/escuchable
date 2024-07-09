<?php

use App\Http\Controllers\Api\EpisodeController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\FollowedController;
use App\Http\Controllers\Api\FollowFeedController;
use App\Http\Controllers\Api\LikeEpisodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\NewReleasesController;
use App\Http\Controllers\Api\PlayedController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UpdateFeedsController;

/**
 * route "/register"
 * @method "POST"
 */
Route::post('/register', RegisterController::class)->name('register');

/**
 * route "/login"
 * @method "POST"
 */
Route::post('/login', LoginController::class)->name('login');


/**
 * route "/logout"
 * @method "POST"
 */
Route::post('/logout', LogoutController::class)->name('logout');

/**
 * Rutas protegidas
 */
Route::middleware('auth:api')->group(function () {
    /**
     * route "/user"
     * @method "GET"
     */
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    /**
     * route "/search"
     * @method "GET"
     */
    Route::get('/search', SearchController::class)->name('search');
    Route::post('/search', SearchController::class)->name('search');

    /**
     * route "/feed"
     * @method "GET"
     */
    Route::get('/feed', FeedController::class)->name('feed');

    /**
     * route "/episode"
     * @method "GET"
     */
    Route::get('/episode', EpisodeController::class)->name('episode');

    /**
     * route "/follow-feed"
     * @method "POST"
     */
    Route::post('/follow-feed', FollowFeedController::class)->name('follow-feed');

    /**
     * route "/like-episode"
     * @method "POST"
     */
    Route::post('/like-episode', LikeEpisodeController::class)->name('like-episode');

    /**
     * route "/playlist"
     * @method "POST"
     */
    Route::post('/playlist', PlaylistController::class)->name('playlist');
    Route::get('/playlist', PlaylistController::class)->name('playlist');

    /**
     * route "/playlist"
     * @method "GET"
     */
    Route::get('/new-releases', NewReleasesController::class)->name('playlist');

    /**
     * route "/followed"
     * @method "GET"
     */
    Route::get('/followed', FollowedController::class)->name('followed');

    /**
     * route "/update"
     * @method "GET"
     */
    Route::get('/update-feeds', UpdateFeedsController::class)->name('update-feeds');

    /**
     * route "/played"
     * @method "POST"
     */
    Route::post('/played', PlayedController::class)->name('played');
});
