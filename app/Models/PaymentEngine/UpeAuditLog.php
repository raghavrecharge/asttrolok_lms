<?php

namespace App\Models\PaymentEngine;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UpeAuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'upe_audit_log';

    protected static function boot()
    {
        parent::boot();

        static::updating(function () {
            throw new \RuntimeException('Audit log entries are immutable. Updates are not allowed.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Audit log entries are immutable. Deletes are not allowed.');
        });
    }

    protected $fillable = [
        'actor_id',
        'actor_role',
        'action',
        'entity_type',
        'entity_id',
        'old_state',
        'new_state',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_state' => 'array',
        'new_state' => 'array',
        'created_at' => 'datetime',
    ];

    // ── Relationships ──

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    // ── Factory Method ──

    public static function record(
        int $actorId,
        string $actorRole,
        string $action,
        string $entityType,
        int $entityId,
        ?array $oldState = null,
        ?array $newState = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return self::create([
            'actor_id' => $actorId,
            'actor_role' => $actorRole,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_state' => $oldState,
            'new_state' => $newState,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    // ── Scopes ──

    public function scopeForEntity($query, string $type, int $id)
    {
        return $query->where('entity_type', $type)->where('entity_id', $id);
    }

    public function scopeByActor($query, int $actorId)
    {
        return $query->where('actor_id', $actorId);
    }

    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
