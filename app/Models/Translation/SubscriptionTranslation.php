<?php

namespace App\Models\Translation;

use Illuminate\Database\Eloquent\Model;

class SubscriptionTranslation extends Model
{
    protected $table = 'subscription_translations';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
