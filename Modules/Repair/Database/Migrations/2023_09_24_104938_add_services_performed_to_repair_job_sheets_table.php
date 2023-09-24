<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServicesPerformedToRepairJobSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('repair_job_sheets', function (Blueprint $table) {
            $table->longText('services_performed')->nullable()->after('service_costs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('repair_job_sheets', function (Blueprint $table) {
            $table->dropColumn('services_performed');
        });
    }
}
