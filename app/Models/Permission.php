<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    public function sections()
    {
        return $this->belongsTo('App\Models\Section', 'section_id');
    }
}
