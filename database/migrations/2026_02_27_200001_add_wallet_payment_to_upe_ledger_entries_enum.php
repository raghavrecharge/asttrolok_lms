<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddWalletPaymentToUpeLedgerEntriesEnum extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE upe_ledger_entries MODIFY COLUMN entry_type ENUM(
            'payment', 'refund', 'discount', 'adjustment_in', 'adjustment_out',
            'referral_bonus', 'installment_payment', 'subscription_charge',
            'penalty', 'write_off', 'wallet_payment'
        ) NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE upe_ledger_entries MODIFY COLUMN entry_type ENUM(
            'payment', 'refund', 'discount', 'adjustment_in', 'adjustment_out',
            'referral_bonus', 'installment_payment', 'subscription_charge',
            'penalty', 'write_off'
        ) NOT NULL");
    }
}
