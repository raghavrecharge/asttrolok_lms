<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add offline payment notification templates
        $templates = [
            [
                'title' => 'Payment Approved - Course Access Granted',
                'template' => 'offline_payment_approved',
                'listId' => 0,
            ],
            [
                'title' => 'Payment Verification Failed',
                'template' => 'offline_payment_rejected',
                'listId' => 0,
            ],
            [
                'title' => 'Offline Payment Approved',
                'template' => 'offline_payment_approved_admin',
                'listId' => 0,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('notification_templates')->insert($template);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('notification_templates')
            ->whereIn('template', [
                'offline_payment_approved',
                'offline_payment_rejected', 
                'offline_payment_approved_admin'
            ])
            ->delete();
    }
};
