<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('upe_referrals')) { return; }
        Schema::create('upe_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_user_id');
            $table->string('referral_code', 32)->unique();
            $table->unsignedBigInteger('referred_user_id')->nullable();
            $table->unsignedBigInteger('referred_sale_id')->nullable();
            $table->enum('bonus_type', ['wallet_credit', 'discount_credit'])->default('wallet_credit');
            $table->decimal('bonus_amount', 15, 2)->default(0);
            $table->enum('bonus_status', ['pending', 'credited', 'expired', 'ineligible'])->default('pending');
            $table->unsignedBigInteger('bonus_ledger_entry_id')->nullable();
            $table->timestamp('credited_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('referrer_user_id');
            $table->index('referred_user_id');
            $table->index('referred_sale_id');
            $table->index('bonus_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upe_referrals');
    }
};
