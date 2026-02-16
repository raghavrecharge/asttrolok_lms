<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use App\Models\Traits\SequenceContent;

class WebinarAssignment extends Model implements TranslatableContract
{
    use Translatable;
    use SequenceContent;

    protected $table = 'webinar_assignments';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public $translatedAttributes = ['title', 'description'];

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function getDescriptionAttribute()
    {
        return getTranslateAttributeValue($this, 'description');
    }

    public function webinar()
    {
        return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    }

    public function chapter()
    {
        return $this->belongsTo('App\Models\WebinarChapter', 'chapter_id', 'id');
    }

    public function attachments()
    {
        return $this->hasMany('App\Models\WebinarAssignmentAttachment', 'assignment_id', 'id');
    }

    public function assignmentHistory()
    {
        return $this->hasOne('App\Models\WebinarAssignmentHistory', 'assignment_id', 'id');
    }

    public function instructorAssignmentHistories()
    {
        return $this->hasMany('App\Models\WebinarAssignmentHistory', 'assignment_id', 'id');
    }

    public function getAssignmentHistoryByStudentId($studentId)
    {
        return $this->assignmentHistory()
            ->where('student_id', $studentId)
            ->first();
    }

    public function getDeadlineTimestamp($user = null)
    {
        $deadline = null;

        if (empty($user)) {
            $user = auth()->user();
        }

        if (!empty($this->deadline)) {
            $webinar = \App\Models\Webinar::find($this->webinar_id);
            $sale = $webinar ? $webinar->getSaleItem($user) : null;

            if (!empty($sale)) {
                $purchaseTimestamp = $sale->created_at instanceof \Carbon\Carbon
                    ? $sale->created_at->timestamp
                    : (int) $sale->created_at;
                $deadline = strtotime("+{$this->deadline} days", $purchaseTimestamp);
            } else {
                $deadline = false;
            }
        }

        return $deadline;
    }
}
