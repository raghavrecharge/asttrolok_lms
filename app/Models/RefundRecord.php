<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundRecord extends Model
{
    protected $table = 'refund_records';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    protected $casts = [
        // 'refund_amount' => 'decimal:2',
        'processed_at' => 'datetime',
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

    public function supportRequest()
    {
        return $this->belongsTo('App\Models\NewSupportForAsttrolok', 'support_ticket_id', 'id');
    }

    public function processedBy()
    {
        return $this->belongsTo('App\User', 'processed_by', 'id');
    }

    // Helper methods
    public function getFormattedRefundAmount()
    {
        return '₹' . number_format($this->refund_amount, 2);
    }

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

    public function scopeByWebinar($query, $webinarId)
    {
        return $query->where('webinar_id', $webinarId);
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
