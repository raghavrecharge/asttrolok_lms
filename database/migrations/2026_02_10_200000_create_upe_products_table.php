<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upe_products', function (Blueprint $table) {
            $table->id();
            $table->enum('product_type', [
                'course_live', 'course_video', 'webinar',
                'subscription', 'event', 'bundle',
            ]);
            $table->unsignedInteger('external_id')->comment('FK to webinars.id, bundles.id, etc.');
            $table->decimal('base_fee', 15, 2)->comment('Immutable base price');
            $table->char('currency', 3)->default('INR');
            $table->unsignedInteger('validity_days')->nullable()->comment('Days of access after purchase');
            $table->boolean('is_upgradeable')->default(false);
            $table->unsignedBigInteger('upgrade_policy_id')->nullable();
            $table->boolean('adjustment_eligible')->default(false);
            $table->decimal('adjustment_max_percent', 5, 2)->default(80.00);
            $table->enum('status', ['active', 'archived', 'draft'])->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['product_type', 'external_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upe_products');
    }
};
