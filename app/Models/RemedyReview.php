<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemedyReview extends Model
{
    protected $table = 'remedy_reviews';
    public $timestamps = false;

    protected $guarded = ['id'];

    public function remedy()
    {
        return $this->belongsTo('App\Models\Remedy', 'remedy_id', 'id');
    }

    public function bundle()
    {
        return $this->belongsTo('App\Models\Bundle', 'bundle_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'creator_id', 'id');
    }

    // public function comments()
    // {
    //     return $this->hasMany('App\Models\Comment', 'review_id', 'id');
    // }
}
