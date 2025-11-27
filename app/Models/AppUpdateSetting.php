<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppUpdateSetting extends Model
{
    protected $fillable = [
        'latest_version_android',
        'latest_version_ios',
        'force_update_android',
        'force_update_ios',
        'optional_update',
        'force_update_message',
        'optional_update_message',
        'delay_seconds',
        'playstore_url',
        'appstore_url',
    ];

    protected $casts = [
        'force_update_android' => 'boolean',
        'force_update_ios' => 'boolean',
        'optional_update' => 'boolean',
        'delay_seconds' => 'integer',
    ];
}