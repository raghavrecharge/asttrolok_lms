<?php

namespace App\Models\Translation;

use Illuminate\Database\Eloquent\Model;

class RemedyChapterTranslation extends Model
{
    protected $table = 'remedy_chapter_translations';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
