<?php

namespace App\Models;

use App\User;
use App\Models\Webinar;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Model;

class OfflinePaymentRecord extends Model
{
    protected $table = 'offline_payment_records';

    protected $fillable = [
        'support_ticket_id',
        'sale_id',
        'user_id',
        'webinar_id',
        'amount',
        'payment_date',
        'payment_location',
        'receipt_number',
        'approved_by',
        'approved_at',
        'ticket_number',
        'status',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function supportTicket()
    {
        return $this->belongsTo(NewSupportForAsttrolok::class, 'support_ticket_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function webinar()
    {
        return $this->belongsTo(Webinar::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Helper methods
    public function getStatusBadgeClass()
    {
        $classes = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
        ];

        return $classes[$this->status] ?? 'secondary';
    }

    public function getFormattedAmount()
    {
        return '₹' . number_format($this->amount, 2);
    }

    public function getPaymentLocationLabel()
    {
        $locations = [
            'office' => 'Office',
            'bank' => 'Bank',
            'online_center' => 'Online Center',
            'other' => 'Other',
        ];

        return $locations[$this->payment_location] ?? $this->payment_location;
    }
}
