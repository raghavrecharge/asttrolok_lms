<?php

namespace App\Models\Translation;

use Illuminate\Database\Eloquent\Model;

class RemedyTranslation extends Model
{
    protected $table = 'remedy_translations';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
