<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personalizedcategory extends Model
{
   protected $fillable = [
        'name',
        'ellipse',
        'vector',
        'frame',
        'group_class',
        'ellipse_class',
        'vector_class',
        'group_inner_class',
        'frame_class',
        'link'
    ];
}
