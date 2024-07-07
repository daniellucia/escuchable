<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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

        $episodes = Episode::whereIn('feed_id', $feeds)->orderBy('published_at', 'desc')->limit(30)->get();
        return $episodes;
    }

    public function followed()
    {
        $followed = [];
        foreach($this->followings as $following) {
            $followed[] = $following->followable;
        }

        return $followed;
    }

    /**
     * Retorna la playlist del usuario
     *
     * @return void
     */
    public function playlist()
    {
        return $this->hasMany(Playlist::class);
    }

    /**
     * Agrega un feed a la playlist del usuario
     *
     * @param Feed $feed
     * @return void
     */
    public function addToPlaylist(Feed $feed)
    {
        Playlist::add($this->id, $feed->id);

        return $this->playlist;
    }

    /**
     * Elimina un feed de la playlist del usuario
     *
     * @param Feed $feed
     * @return void
     */
    public function removeFromPlaylist(Feed $feed)
    {
        Playlist::remove($this->id, $feed->id);

        return $this->playlist;
    }

    /**
     * Verifica si un feed está en la playlist del usuario
     *
     * @param Feed $feed
     * @return void
     */
    public function hasInPlaylist(Feed $feed)
    {
        return $this->playlist->contains('feed_id', $feed->id);
    }
}
