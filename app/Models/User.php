<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Events\FeedSaved;
use Illuminate\Database\Eloquent\Collection;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Overtrue\LaravelFollow\Traits\Follower;
use Overtrue\LaravelFavorite\Traits\Favoriter;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, Follower, Favoriter;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function new_releases()
    {
        foreach ($this->followings()->with('followable')->get() as $following) {
            $feeds[] = $following->followable->id;
        }

        $episodes = Episode::whereIn('feed_id', $feeds)->orderBy('published_at', 'desc')->limit(100)->get();
        return $episodes;
    }

    public function followed()
    {
        $followed = [];
        foreach ($this->followings as $following) {
            $followed[] = $following->followable->id;
        }

        $podcasts_followed = Feed::whereIn('id', $followed)->orderBy('last_episode', 'desc')->get();

        return $podcasts_followed;
    }


    public function recommends()
    {
        $recommends = Recommend::where('user_id', $this->id)->get()->pluck('feed_id')->all();
        return Feed::whereIn('id', $recommends)->orderBy('last_episode', 'desc')->get();
    }

    /**
     * Retorna la playlist del usuario
     *
     * @return void
     */
    public function playlist()
    {

        $episodes = [];
        $playlist = Playlist::where('user_id', $this->id)->get();
        foreach ($playlist as $playlist) {
            $episodes[] = $playlist->episode;
        }

        return collect($episodes);
    }

    /**
     * Agrega un feed a la playlist del usuario
     *
     * @param Episode $episode
     * @return void
     */
    public function addToPlaylist(Episode $episode)
    {
        Playlist::add($this->id, $episode->id);

        return $this->playlist();
    }

    /**
     * Elimina un feed de la playlist del usuario
     *
     * @param Episode $episode
     * @return void
     */
    public function removeFromPlaylist(Episode $episode)
    {
        Playlist::remove($this->id, $episode->id);

        return $this->playlist();
    }

    /**
     * Verifica si un feed está en la playlist del usuario
     *
     * @param Episode $episode
     * @return void
     */
    public function hasInPlaylist(Episode $episode)
    {
        return $this->playlist()->contains('id', $episode->id);
    }

    public function update_feeds()
    {
        foreach ($this->followed() as $feed) {
            event(new FeedSaved($feed));
        }

        return true;
    }

    public function played(Episode $episode, int $time, bool $finished)
    {
        $played = EpisodePlayed::where('user_id', $this->id)->where('episode_id', $episode->id)->first();
        if (!$played) {
            $played = new EpisodePlayed();
            $played->user_id = $this->id;
            $played->episode_id = $episode->id;
        }

        $played->time = $time;
        $played->finished = $finished ? 1 : 0;
        $played->save();

        return $played;
    }
}
