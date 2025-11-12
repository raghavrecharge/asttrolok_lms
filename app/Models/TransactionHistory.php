<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{
    protected $table = 'transaction_history';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
