<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflinePayment extends Model
{
    // Status constants
    public static $pending = 'pending';
    public static $waiting = 'waiting';
    public static $approved = 'approved';
    public static $reject = 'reject';
    public static $failed = 'failed';

    public $timestamps = true;

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'webinar_id',
        'amount',
        'bank',
        'reference_number',
        'utr_number',
        'attachment',
        'screenshot_path',
        'pay_date',
        'status',
        'admin_remark',
        'sale_id',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'pay_date' => 'date',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function webinar()
    {
        return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    }

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale', 'sale_id', 'id');
    }

    public function processedBy()
    {
        return $this->belongsTo('App\User', 'processed_by', 'id');
    }

    public function offlineBank()
    {
        return $this->belongsTo('App\Models\OfflineBank', 'offline_bank_id', 'id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::$pending);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', self::$waiting);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::$approved);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::$reject);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::$failed);
    }

    // Helper methods
    public function getStatusBadgeClass()
    {
        $classes = [
            self::$pending => 'warning',
            self::$waiting => 'info',
            self::$approved => 'success',
            self::$reject => 'danger',
            self::$failed => 'danger',
        ];

        return $classes[$this->status] ?? 'secondary';
    }

    public function getStatusLabel()
    {
        $labels = [
            self::$pending => 'Pending',
            self::$waiting => 'Waiting',
            self::$approved => 'Approved',
            self::$reject => 'Rejected',
            self::$failed => 'Failed',
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    public function getFormattedAmount()
    {
        return '₹' . number_format($this->amount, 2);
    }

    public function getAttachmentPath()
    {
        if ($this->attachment) {
            return '/store/' . $this->user_id . '/offlinePayments/' . $this->attachment;
        }
        
        if ($this->screenshot_path) {
            return $this->screenshot_path;
        }
        
        return null;
    }

    public function getAttachmentUrl()
    {
        $path = $this->getAttachmentPath();
        return $path ? asset($path) : null;
    }

    public function isProcessed()
    {
        return in_array($this->status, [self::$approved, self::$reject, self::$failed]);
    }

    public function canBeApproved()
    {
        return in_array($this->status, [self::$pending, self::$waiting]);
    }

    public function getUtrNumber()
    {
        return $this->utr_number ?: $this->reference_number;
    }
}
