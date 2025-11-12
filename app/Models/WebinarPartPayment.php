<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebinarPartPayment extends Model
{
    protected $table = "webinar_part_payment";
    public $timestamps = false;
    protected $guarded = ['id'];
public function webinar()
    {
        return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    }
    
    public function installment()
    {
        return $this->belongsTo('App\Models\InstallmentOrderPayment', 'installment_id', 'id');
    }
    

    public function buyer()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function seller()
    {
        return [];
    }

    public function meeting()
    {
        return [];
    }

    public function subscribe()
    {
        return [];
    }

    public function promotion()
    {
        return [];
    }

    public function registrationPackage()
    {
       return null;
    }

    public function order()
    {
        return null;
    }

    public function ticket()
    {
       return null;
    }

    public function saleLog()
    {
       return null;
    }

    public function productOrder()
    {
        return null;
    }

    public function gift()
    {
        return null;
    }

    public function installmentOrderPayment()
    {
        return null;
    }
    // public function webinar()
    // {
    //     return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    // }

    // public function user()
    // {
    //     return $this->belongsTo('App\User', 'user_id', 'id');
    // }
}
