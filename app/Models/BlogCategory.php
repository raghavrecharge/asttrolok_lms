<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    protected $table = 'blog_categories';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function blog()
    {
        return $this->hasMany('App\Models\Blog', 'category_id', 'id');
    }

     public function getUrl()
    {
        $baseUrl = config('app.manual_base_url');

        return $baseUrl . '/blog/categories/' . $this->slug;
    }
}
