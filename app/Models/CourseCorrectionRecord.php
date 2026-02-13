<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseCorrectionRecord extends Model
{
    protected $table = 'course_correction_records';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function wrongCourse()
    {
        return $this->belongsTo('App\Models\Webinar', 'wrong_course_id', 'id');
    }

    public function correctCourse()
    {
        return $this->belongsTo('App\Models\Webinar', 'correct_course_id', 'id');
    }

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale', 'sale_id', 'id');
    }

    public function supportRequest()
    {
        return $this->belongsTo('App\Models\NewSupportForAsttrolok', 'support_ticket_id', 'id');
    }

    public function processedBy()
    {
        return $this->belongsTo('App\User', 'processed_by', 'id');
    }

    // Helper methods
    public function getStatusLabel()
    {
        return 'Processed';
    }

    public function getStatusBadgeClass()
    {
        return 'success';
    }

    public function getProcessedAtFormatted()
    {
        return $this->processed_at ? $this->processed_at->format('j M Y H:i') : 'N/A';
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByWrongCourse($query, $webinarId)
    {
        return $query->where('wrong_course_id', $webinarId);
    }

    public function scopeByCorrectCourse($query, $webinarId)
    {
        return $query->where('correct_course_id', $webinarId);
    }

    public function scopeProcessed($query)
    {
        return $query->whereNotNull('processed_at');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('processed_at', '>=', now()->subDays($days));
    }
}
