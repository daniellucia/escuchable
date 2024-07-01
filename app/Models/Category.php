<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Category extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'visible',
        'count'
    ];

    /**
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public static function obtain(string $category_name)
    {

        $data = [
            'name' => $category_name,
            'visible' => true,
        ];

        $category = Category::updateOrCreate(
            ['name' => $category_name],
            $data
        );

        return $category;
    }
}
