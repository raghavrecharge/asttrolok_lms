<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedBook extends Model
{
   protected $table = 'featured_books';
   protected $fillable = [
    'title','subtitle','description',
    'pages','copies_sold',
    'main_image','bg_image_1','bg_image_2',
    'is_active'
];

}
