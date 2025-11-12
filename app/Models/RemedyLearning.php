<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemedyLearning extends Model
{
    protected $table = 'remedy_learning';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];


}
