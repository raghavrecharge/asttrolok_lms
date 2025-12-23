<?php

namespace App\Models;

use App\Notifications\SendVerificationEmailCode;
use App\Notifications\SendVerificationSMSCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Verification extends Model
{
    use Notifiable;

    protected $table = 'verifications';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    const EXPIRE_TIME = 3600;

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function sendEmailCode()
    {

        $this->notify(new SendVerificationEmailCode($this));

    }

    public function sendSMSCode()
    {
        $this->notify(new SendVerificationSMSCode($this));
    }

    public function isExpired(): bool
    {
        return time() > $this->expired_at;
    }

    /**
     * Check if already verified
     */
    public function isVerified(): bool
    {
        return !empty($this->verified_at);
    }
}
