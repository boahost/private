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
        Schema::table('transaction_payments', function (Blueprint $table) {
            $table->enum('status', ['cash', 'card', 'cheque', 'bank_transfer', 'custom_pay_1', 'custom_pay_2', 'custom_pay_3', 'other', 'boleto', 'pix_efi'])->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_payments', function (Blueprint $table) {
            //
        });
    }
}
