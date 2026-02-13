<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorAccessRequest extends Model
{
    protected $fillable = [
        'user_id',
        'webinar_id',
        'requested_mentor_id',
        'mentor_change_reason',
        'status',
        'admin_notes',
        'approved_by',
        'approved_at',
        'support_request_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function webinar()
    {
        return $this->belongsTo(Webinar::class);
    }

    public function requestedMentor()
    {
        return $this->belongsTo(User::class, 'requested_mentor_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
