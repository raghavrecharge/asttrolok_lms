<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('event_payment_links', function (Blueprint $table) {
            $table->string('link_token')->nullable()->change();
            $table->string('payment_link')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_payment_links', function (Blueprint $table) {
            $table->string('link_token')->nullable(false)->change();
            $table->string('payment_link')->nullable(false)->change();
        });
    }
};
