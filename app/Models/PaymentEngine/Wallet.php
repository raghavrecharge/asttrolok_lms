<?php

namespace App\Models\PaymentEngine;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallets';

    protected $fillable = [
        'user_id',
        'balance',
        'currency',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    // ── Constants ──

    const CURRENCY_INR = 'INR';

    // ── Relationships ──

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class)->orderByDesc('created_at');
    }

    // ── Helpers ──

    public function canAfford(float $amount): bool
    {
        return (float) $this->balance >= $amount;
    }

    public function hasBalance(): bool
    {
        return (float) $this->balance > 0;
    }
}
