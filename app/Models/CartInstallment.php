<?php

namespace App\Models;

use App\Mixins\Cart\CartItemInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CartInstallment extends Model
{
    protected $table = 'cart_installment';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
