<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'episode_id',
        'order'
    ];

    protected $with = ['episode'];

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }

    public static function add(int $user_id, int $episode_id)
    {
        $playlist = self::where('user_id', $user_id)->where('episode_id', $episode_id)->first();
        if ($playlist) {
            return $playlist;
        }

        $playlist = new self();
        $playlist->user_id = $user_id;
        $playlist->episode_id = $episode_id;
        $playlist->order = self::where('user_id', $user_id)->max('order') + 1;
        $playlist->save();

        return $playlist;
    }

    public static function remove(int $user_id, int $episode_id)
    {
        $playlist = self::where('user_id', $user_id)->where('episode_id', $episode_id)->first();
        if ($playlist) {
            $playlist->delete();
        }
    }
}
