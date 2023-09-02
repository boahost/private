<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');

            $table->string('certificate')->nullable();
            $table->string('password', 100)->default('');
            $table->string('integration', 100);
            $table->string('payee_code', 100)->nullable();
            $table->string('key_client_id', 100)->nullable();
            $table->string('key_client_secret', 100)->nullable();
            $table->string('pix_key', 100)->nullable();
            $table->string('pix_split_plan', 100)->nullable();

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
        Schema::dropIfExists('integrations');
    }
}
