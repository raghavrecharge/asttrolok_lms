<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportAuditLog extends Model
{
    protected $table = 'support_audit_logs';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function supportRequest()
    {
        return $this->belongsTo(NewSupportForAsttrolok::class, 'support_request_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public static function log($supportRequestId, $userId, $action, $role, $oldStatus, $newStatus, $metadata = null, $ipAddress = null)
    {
        return self::create([
            'support_request_id' => $supportRequestId,
            'user_id' => $userId,
            'action' => $action,
            'role' => $role,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'metadata' => $metadata,
            'ip_address' => $ipAddress,
            'created_at' => now(),
        ]);
    }
}
