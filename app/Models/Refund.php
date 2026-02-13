<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $table = 'refunds';

    protected $guarded = ['id'];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public static $pending = 'pending';
    public static $processed = 'processed';
    public static $failed = 'failed';

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function installmentOrder()
    {
        return $this->belongsTo(InstallmentOrder::class, 'installment_order_id');
    }

    public function supportRequest()
    {
        return $this->belongsTo(NewSupportForAsttrolok::class, 'support_request_id');
    }

    public function processedByUser()
    {
        return $this->belongsTo(\App\User::class, 'processed_by');
    }
}
