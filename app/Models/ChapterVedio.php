<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WebinarChapter; // Import the related model

class ChapterVedio extends Model
{
    protected $table = 'chapter_videos';

    public $timestamps = true;

    protected $fillable = [
        'webinar_chapter_id',
        'video_url',
    ];

    public function chapter()
    {
        return $this->belongsTo(WebinarChapter::class, 'webinar_chapter_id');
    }
}
