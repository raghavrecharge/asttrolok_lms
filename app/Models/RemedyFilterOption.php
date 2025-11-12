<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemedyFilterOption extends Model
{
    protected $table = 'remedy_filter_option';
    public $timestamps = false;

    protected $guarded = ['id'];
}
