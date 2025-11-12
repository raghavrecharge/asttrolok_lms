<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionsHistoryRazorpay extends Model
{
    protected $table = 'transactions_history_razorpay';

    protected $fillable = [
        'user_id',
        'name',
        'number',
        'email',
        'amount',
        'razorpay_payment_id',
        'razorpay_description',
    ];
}
