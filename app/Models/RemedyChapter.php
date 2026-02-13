<?php

namespace App\Models;

use App\Models\Traits\SequenceContent;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class RemedyChapter extends Model implements TranslatableContract
{
    use Translatable;
    use SequenceContent;

    protected $table = 'remedy_chapters';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    static $chapterFile = 'file';
    static $chapterSession = 'session';
    static $chapterTextLesson = 'text_lesson';

    static $chapterActive = 'active';
    static $chapterInactive = 'inactive';

    static $chapterTypes = ['file', 'session', 'text_lesson'];

    static $chapterStatus = ['active', 'inactive'];

    public $translatedAttributes = ['title'];

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function sessions()
    {
        return $this->hasMany('App\Models\Session', 'chapter_id', 'id');
    }

    public function files()
    {
        return $this->hasMany('App\Models\Refile', 'chapter_id', 'id');
    }

    public function textLessons()
    {
        return $this->hasMany('App\Models\TextLesson', 'chapter_id', 'id');
    }

    public function chapterItems()
    {
        return $this->hasMany('App\Models\RemedyChapterItem', 'chapter_id', 'id');
    }

    public function remedy()
    {
        return $this->hasOne('App\Models\Remedy', 'remedy_id', 'id');
    }

    public function getDuration()
    {
        $time = 0;

        $time += $this->sessions->sum('duration');

        return $time;
    }

    public function getTopicsCount($withQuiz = false)
    {
        $count = 0;

        $count += $this->files->where('status', 'active')->count();

        return $count;
    }
}
