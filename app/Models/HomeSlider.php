<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSlider extends Model
{
    protected $table = 'home_sliders';
    protected $guarded = ['id'];
    public $timestamps = false;

    protected $fillable = ['title','home_hero2','personalization','locale','description','button_text','button_url','button_color','hero_background','hero_vector','has_lottie','status'];
}
