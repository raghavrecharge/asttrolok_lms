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
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            $table->unsignedBigInteger('webinar_id')->nullable()->change();
            $table->string('support_scenario')->nullable()->change();
            $table->string('flow_type')->nullable()->change();
            $table->string('purchase_status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            $table->unsignedBigInteger('webinar_id')->nullable(false)->change();
            $table->string('support_scenario')->nullable(false)->change();
            $table->string('flow_type')->nullable(false)->change();
            $table->string('purchase_status')->nullable(false)->change();
        });
    }
};
