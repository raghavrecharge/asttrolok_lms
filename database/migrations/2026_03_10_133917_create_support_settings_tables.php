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
        // Support settings table
        Schema::create('support_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'notification_settings'
            $table->longText('value'); // JSON data
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->index('key');
        });

        // Support settings audit log
        Schema::create('support_settings_audit', function (Blueprint $table) {
            $table->id();
            $table->longText('settings'); // JSON data
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            
            $table->index('changed_at');
            $table->index('changed_by');
        });

        // Notification history table
        Schema::create('notification_history', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'email', 'sms', 'in_app'
            $table->string('recipient'); // email address or phone number
            $table->string('subject')->nullable();
            $table->longText('content');
            $table->string('status'); // 'sent', 'failed', 'pending'
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('triggered_by')->nullable(); // user who triggered the notification
            $table->string('trigger_type')->nullable(); // 'ticket_created', 'ticket_assigned', etc.
            $table->unsignedBigInteger('trigger_id')->nullable(); // related ticket ID, etc.
            $table->timestamps();
            
            $table->index('type');
            $table->index('status');
            $table->index('trigger_type');
            $table->index('created_at');
        });

        // Insert default settings
        DB::table('support_settings')->insert([
            [
                'key' => 'notification_settings',
                'value' => json_encode([
                    'sms' => [
                        'enabled' => false,
                        'critical' => true,
                        'newTickets' => false,
                        'escalation' => true,
                        'afterHours' => false,
                        'phoneNumbers' => []
                    ],
                    'email' => [
                        'enabled' => true,
                        'assignment' => true,
                        'status' => true,
                        'digest' => true,
                        'weekly' => false,
                        'recipients' => []
                    ],
                    'general' => [
                        'businessHoursStart' => '09:00',
                        'businessHoursEnd' => '18:00',
                        'quietHoursEnabled' => false,
                        'weekendNotifications' => false,
                        'maxFrequency' => '10',
                        'priorityHigh' => true,
                        'priorityMedium' => true,
                        'priorityLow' => false
                    ]
                ]),
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_history');
        Schema::dropIfExists('support_settings_audit');
        Schema::dropIfExists('support_settings');
    }
};
