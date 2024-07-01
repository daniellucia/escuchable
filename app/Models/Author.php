<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public static function obtain(string $author_name)
    {

        $data = [
            'name' => $author_name,
            'description' => '',
        ];

        $author = self::updateOrCreate(
            ['name' => $author_name],
            $data
        );

        return $author;
    }
}
