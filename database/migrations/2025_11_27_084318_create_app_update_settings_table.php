<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('app_update_settings')) {
            return;
        }
        Schema::create('app_update_settings', function (Blueprint $table) {
            $table->id();
            $table->string('latest_version_android')->default('1.0.0');
            $table->string('latest_version_ios')->default('1.0.0');
            $table->boolean('force_update_android')->default(false);
            $table->boolean('force_update_ios')->default(false);
            $table->boolean('optional_update')->default(false);
            $table->text('force_update_message')->nullable();
            $table->text('optional_update_message')->nullable();
            $table->integer('delay_seconds')->default(3);
            $table->string('playstore_url')->nullable();
            $table->string('appstore_url')->nullable();
            $table->timestamps();
        });
        
        // Insert default record
        DB::table('app_update_settings')->insert([
            'latest_version_android' => '1.0.0',
            'latest_version_ios' => '1.0.0',
            'force_update_android' => false,
            'force_update_ios' => false,
            'optional_update' => false,
            'force_update_message' => 'Please update the app to continue using.',
            'optional_update_message' => 'A new version is available. Update now for better experience.',
            'delay_seconds' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('app_update_settings');
    }
};