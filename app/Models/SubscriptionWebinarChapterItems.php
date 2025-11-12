<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionWebinarChapterItems extends Model
{
    protected $table = 'subscription_webinar_chapter_items';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public function quiz()
    {
        return $this->belongsTo(\App\Models\Quiz::class, 'item_id', 'id');
    }
     public function fileTransaction()
    {
        return $this->belongsTo(\App\Models\FileTransaction::class, 'item_id', 'file_id');
    }
    public function textLesson()
{
    return $this->belongsTo(\App\Models\TextLesson::class, 'item_id', 'id');
}

public function session()
{
    return $this->belongsTo(\App\Models\Session::class, 'item_id', 'id');
}

public function assignment()
{
    return $this->belongsTo(\App\Models\Assignment::class, 'item_id', 'id');
}

public function file()
{
    return $this->belongsTo(File::class, 'item_id', 'id');
}

public function getItemAttribute()
{
   switch ($this->type) {
        case 'file':
            return $this->file;
        case 'quiz':
            return $this->quiz;
        case 'text_lesson':
            return $this->textLesson;
        case 'session':
            return $this->session;
        case 'assignment':
            return $this->assignment;
        default:
            return null;
    }
}
public function getIconByType($type = null)
    {
        $icon = 'file';

        if (empty($type)) {
            $type = $this->file_type;
        }

        if (!empty($type)) {
            if (in_array($type, ['pdf', 'powerpoint', 'document'])) {
                $icon = 'file-text';
            } else if (in_array($type, ['sound'])) {
                $icon = 'volume-2';
            } else if (in_array($type, ['video'])) {
                $icon = 'film';
            } else if (in_array($type, ['image'])) {
                $icon = 'image';
            } else if (in_array($type, ['archive'])) {
                $icon = 'archive';
            }
        }

        return $icon;
    }

}
