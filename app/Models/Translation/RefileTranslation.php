<?php

namespace App\Models\Translation;

use Illuminate\Database\Eloquent\Model;

class RefileTranslation extends Model
{
    protected $table = 'refile_translations';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
