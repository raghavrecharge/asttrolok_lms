<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrendCategory extends Model
{
    protected $table = 'trend_categories';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'category_id', 'id');
    }

    public function getIcon()
    {
        return $this->icon;
    }
}
