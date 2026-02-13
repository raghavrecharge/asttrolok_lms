<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upe_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique()->nullable()->comment('Coupon code; NULL for auto-applied');
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('value', 15, 2)->comment('Percent (0-100) or fixed amount');
            $table->decimal('max_discount_amount', 15, 2)->nullable()->comment('Cap for percentage discounts');
            $table->decimal('min_order_amount', 15, 2)->nullable();
            $table->enum('scope', ['global', 'product', 'category', 'user']);
            $table->json('scope_ids')->nullable()->comment('Array of product/category/user IDs');
            $table->json('allowed_roles')->nullable()->comment('Which roles can create/apply');
            $table->unsignedInteger('max_uses_total')->nullable();
            $table->unsignedInteger('max_uses_per_user')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('stackable')->default(false);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->enum('status', ['active', 'expired', 'disabled'])->default('active');
            $table->timestamps();

            $table->index('status');
            $table->index('scope');
            $table->index(['valid_from', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upe_discounts');
    }
};
