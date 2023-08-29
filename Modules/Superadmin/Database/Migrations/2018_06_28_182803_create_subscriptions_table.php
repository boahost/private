<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->integer('package_id')->unsigned();
            $table->date('start_date')->nullable();
            $table->date('trial_end_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('package_price', 22, 4);
            $table->longText('package_details');
            $table->integer('created_id')->unsigned();
            $table->string('paid_via')->nullable();
            $table->string('payment_transaction_id')->nullable();
            $table->enum('status', ['approved', 'waiting', 'declined'])->default('waiting');

            $table->text('qr_code_base64');
            $table->text('qr_code');
            $table->string('link_boleto', 255)->default('');
            $table->string('numero_cartao', 40)->default('');
            $table->enum('forma_pagamento', ['pix', 'cartao', 'boleto']);
            $table->string('status_mp', 30);

            // alter table subscriptions add column qr_code_base64 text;
            // alter table subscriptions add column qr_code text;
            // alter table subscriptions add column link_boleto varchar(255) default '';
            // alter table subscriptions add column numero_cartao varchar(40) default '';
            // alter table subscriptions add column forma_pagamento enum('pix','cartao','boleto') default NULL;
            // alter table subscriptions add column status_mp varchar(30) default '';

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
