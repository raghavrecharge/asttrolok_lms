<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewSupportForAsttrolokLog extends Model
{
    protected $fillable = [
        'support_request_id',
        'user_id',
        'action',
        'remarks',
        'old_data',
        'new_data',
        'ip_address',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];
}

