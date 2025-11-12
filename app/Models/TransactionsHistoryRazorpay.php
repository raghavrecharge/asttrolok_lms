<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionsHistoryRazorpay extends Model
{
    protected $table = 'transactions_history_razorpay';
    
    protected $fillable = [
        'razorpay_payment_id',
        'razorpay_order_id',
        'razorpay_signature',
        'order_id',
        'payment_type',
        'user_id',
        'name',
        'number',
        'email',
        'amount',
        'status',
        'payment_method',
        'source',
        'processed_at',
        'metadata',
        'razorpay_description',
    ];

    protected $casts = [
        'metadata' => 'array',
        'processed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUnprocessed($query)
    {
        return $query->whereNull('processed_at')
                     ->where('status', 'completed');
    }
}