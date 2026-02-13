<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponCredit extends Model
{
    protected $table = 'coupon_credits';

    protected $guarded = ['id'];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function installmentOrder()
    {
        return $this->belongsTo(InstallmentOrder::class, 'installment_order_id');
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discount_id');
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
