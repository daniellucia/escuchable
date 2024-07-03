<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'feed_id',
        'order'
    ];


    public static function add(int $user_id, int $feed_id)
    {
        $playlist = self::where('user_id', $user_id)->where('feed_id', $feed_id)->first();
        if ($playlist) {
            return $playlist;
        }

        $playlist = new self();
        $playlist->user_id = $user_id;
        $playlist->feed_id = $feed_id;
        $playlist->order = self::where('user_id', $user_id)->max('order') + 1;
        $playlist->save();

        return $playlist;
    }

    public static function remove(int $user_id, int $feed_id)
    {
        $playlist = self::where('user_id', $user_id)->where('feed_id', $feed_id)->first();
        if ($playlist) {
            $playlist->delete();
        }
    }
}
