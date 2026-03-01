<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubAdminActivityLog extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function getDateAttribute()
    {
        return date('j M Y, H:i', $this->created_at);
    }

    public static function log($userId, $action, $description = null, $extra = [])
    {
        $request = request();

        return self::create(array_merge([
            'user_id' => $userId,
            'action' => $action,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'description' => $description,
            'created_at' => time(),
        ], $extra));
    }
}
