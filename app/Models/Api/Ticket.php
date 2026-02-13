<?php

namespace App\Models\Api;

use App\Models\Ticket as Model;

class Ticket extends Model
{

    public function getDetailsAttribute(){

        return [
            'id' => $this->id,
            'title' => $this->title,
            'sub_title' => $this->getSubTitle(),
            'discount' => $this->discount,

            'price_with_ticket_discount' => $this->webinar->price - $this->webinar->getDiscount($this),

            'is_valid' => $this->isValid(),

        ];
    }

    public function webinar()
    {
        return $this->belongsTo('App\Models\Api\Webinar', 'webinar_id', 'id');
    }
}
