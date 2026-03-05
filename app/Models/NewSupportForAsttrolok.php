<?php

namespace App\Models;

use App\User;
use App\Models\Webinar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewSupportForAsttrolok extends Model
{
    protected $table = 'new_support_for_asttrolok';

    protected $fillable = [
        'user_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'support_scenario',
        'webinar_id',
        'title',
        'description',
        'attachments',
        'flow_type',
        'purchase_status',
        'course_purchased_at',
        'course_expires_at',
        'status',

        // scenario fields
        'extension_days',
        'extension_reason',
        'pending_amount',
        'expected_payment_date',
        'mentor_change_reason',
        'relative_description',
        'free_course_reason',
        'cash_amount',
        'payment_date',
        'payment_receipt_number',
        'payment_location',
        'payment_screenshot',
        'requested_installments',
        'installment_amount',
        'restructure_reason',
        'requested_service',
        'service_details',
        // 'refund_amount',
        'refund_reason',
        'purchase_to_refund',
        'bank_account_number',
        'ifsc_code',
        'account_holder_name',
        'coupon_code',
        'original_amount',
        'coupon_apply_reason',
        'wrong_course_id',
        'correct_course_id',
        'correction_reason',
        'temporary_access_days',
        'temporary_access_reason',
        
        // Support handling fields
        'support_handler_id',
        'support_remarks',
        'recommended_action',
        'sub_admin_id',
        'approval_remarks',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        
        // Quick Support Form fields
        'source_course_id',
        'target_course_id',
        'total_users_count',
        'granted_users_count',
        'already_had_access_count',

        'execution_notes',
        'executed_at',
        'execution_result',
    ];
 

    protected $casts = [
        'attachments' => 'array',
        'execution_result' => 'array',
        'course_purchased_at' => 'datetime',
        'course_expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($model) {
        $model->ticket_number = 'AST-' . now()->format('ymdHis') . rand(1000,9999);
    });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function webinar()
    {
        return $this->belongsTo(Webinar::class);
    }

    public function category()
    {
        return $this->belongsTo(SupportCategory::class, 'support_category_id');
    }

    public function supportHandler()
    {
        return $this->belongsTo(User::class, 'support_handler_id');
    }

    public function subAdmin()
    {
        return $this->belongsTo(User::class, 'sub_admin_id');
    }

    public function logs()
    {
        return $this->hasMany(NewSupportForAsttrolokLog::class, 'support_request_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInReview($query)
    {
        return $query->where('status', 'in_review');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function isGuest()
    {
        return is_null($this->user_id);
    }

    public function getRequesterName()
    {
        return $this->isGuest() ? $this->guest_name : ($this->user ? $this->user->full_name : 'N/A');
    }

    public function getRequesterEmail()
    {
        return $this->isGuest() ? $this->guest_email : ($this->user ? $this->user->email : 'N/A');
    }

    public function getStatusBadgeClass()
    {
        $classes = [
            'pending' => 'warning',
            'in_review' => 'info',
            'verified' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            'executed' => 'primary',
            'closed' => 'secondary',
        ];

        return $classes[$this->status] ?? 'secondary';
    }

    public function getFlowTypeLabel()
    {
        $labels = [
            'flow_a' => 'Never Purchased',
            'flow_b' => 'Expired Course',
            'flow_c' => 'Active Course',
            'flow_no_refund' => 'Non-Paid Access (No Refund)',
        ];

        return $labels[$this->flow_type] ?? 'Unknown';
    }
    public function getScenarioLabel()
    {
        $labels = [
            'course_extension' => 'Course Extension',
            'temporary_access' => 'Temporary Access (Pending Payment)',
            'mentor_access' => 'Mentor Access',
            'relatives_friends_access' => 'Relatives/Friends Access',
            'free_course_grant' => 'Free Course Grant',
            'offline_cash_payment' => 'Offline/Cash Payment',
            'installment_restructure' => 'Installment Restructure',
            'new_service_access' => 'New Service Access',
            'refund_payment' => 'Refund Payment',
            'post_purchase_coupon' => 'Post-Purchase Coupon Apply',
            'wrong_course_correction' => 'Wrong Course Correction',
        ];

        if (empty($this->support_scenario)) {
            return 'Awaiting Processing';
        }

        return $labels[$this->support_scenario] ?? 'Other';
    }

    public function restructureRequest()
    {
        return $this->hasOne(InstallmentRestructureRequest::class, 'support_ticket_id');
    }
}