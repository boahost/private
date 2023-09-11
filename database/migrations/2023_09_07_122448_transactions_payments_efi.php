<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TransactionsPaymentsEfi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE transaction_payments MODIFY COLUMN method ENUM('cash', 'card', 'cheque', 'bank_transfer', 'custom_pay_1', 'custom_pay_2', 'custom_pay_3', 'other', 'boleto', 'pix', 'debit', 'pix_efi')");
        DB::statement("ALTER TABLE cash_register_transactions MODIFY COLUMN pay_method ENUM('cash', 'card', 'cheque', 'bank_transfer', 'custom_pay_1', 'custom_pay_2', 'custom_pay_3', 'other', 'boleto', 'pix', 'debit', 'pix_efi')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash_register_transactions', function (Blueprint $table) {
            //
        });
    }
}
