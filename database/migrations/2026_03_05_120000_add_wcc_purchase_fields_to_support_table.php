<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            if (!Schema::hasColumn('new_support_for_asttrolok', 'correction_purchase_type')) {
                $table->string('correction_purchase_type')->nullable()->after('correction_reason');
            }
            if (!Schema::hasColumn('new_support_for_asttrolok', 'correction_installment_id')) {
                $table->unsignedBigInteger('correction_installment_id')->nullable()->after('correction_purchase_type');
            }
            if (!Schema::hasColumn('new_support_for_asttrolok', 'correction_quick_pay_amount')) {
                $table->decimal('correction_quick_pay_amount', 12, 2)->nullable()->after('correction_installment_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            $cols = ['correction_purchase_type', 'correction_installment_id', 'correction_quick_pay_amount'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('new_support_for_asttrolok', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
