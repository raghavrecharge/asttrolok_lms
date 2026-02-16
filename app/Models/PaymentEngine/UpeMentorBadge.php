<?php

namespace App\Models\PaymentEngine;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UpeMentorBadge extends Model
{
    protected $table = 'upe_mentor_badges';

    const STATUS_ACTIVE  = 'active';
    const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'user_id',
        'granted_by',
        'granted_at',
        'revoked_at',
        'status',
        'reason',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    // ── Relationships ──

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grantedByUser()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // ── Static Helpers ──

    public static function hasBadge(int $userId): bool
    {
        return static::where('user_id', $userId)
            ->where('status', self::STATUS_ACTIVE)
            ->exists();
    }

    public static function grant(int $userId, int $grantedBy, ?string $reason = null): self
    {
        return static::updateOrCreate(
            ['user_id' => $userId],
            [
                'granted_by' => $grantedBy,
                'granted_at' => now(),
                'revoked_at' => null,
                'status'     => self::STATUS_ACTIVE,
                'reason'     => $reason,
            ]
        );
    }

    public static function revoke(int $userId, ?string $reason = null): bool
    {
        return (bool) static::where('user_id', $userId)
            ->where('status', self::STATUS_ACTIVE)
            ->update([
                'status'     => self::STATUS_REVOKED,
                'revoked_at' => now(),
                'reason'     => $reason,
            ]);
    }
}
