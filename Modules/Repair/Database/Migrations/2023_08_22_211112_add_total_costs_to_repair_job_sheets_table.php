<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalCostsToRepairJobSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repair_job_sheets_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job_sheets_id')->unsigned();
            $table->foreign('job_sheets_id')->references('id')->on('repair_job_sheets')->onDelete('cascade');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->integer('variation_id')->unsigned();
            $table->foreign('variation_id')->references('id')->on('variations')->onDelete('cascade');
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('unit_price', 22, 4)->comment("Sell price excluding tax")->nullable();
            $table->decimal('unit_price_inc_tax', 22, 4)->comment("Sell price including tax")->nullable();
            $table->decimal('item_tax', 22, 4)->default(0)->comment("Tax for one quantity");
            $table->integer('tax_id')->unsigned()->nullable();
            $table->foreign('tax_id')->references('id')->on('tax_rates')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::table('repair_job_sheets', function (Blueprint $table) {
            $table->decimal('service_costs', 22, 4)->nullable();
            $table->decimal('total_costs', 22, 4)->nullable();
        });

        $this->createProcedure();
    }

    public function createProcedure()
    {
        $sqlFunction = "CREATE PROCEDURE `update_total_costs`(IN param_job_sheets_id INT)
                        BEGIN DECLARE total DECIMAL (22, 4);

                        SELECT
                            COALESCE (SUM(quantity * unit_price_inc_tax), 0)
                        INTO total
                        FROM
                            repair_job_sheets_lines
                        WHERE
                            job_sheets_id = param_job_sheets_id;

                        UPDATE
                            repair_job_sheets
                        SET
                            total_costs = total + COALESCE (service_costs, 0)
                        WHERE
                            id = param_job_sheets_id;

                        END";

        DB::unprepared($sqlFunction);
    }

    public function createTrigger()
    {
        // Define the SQL statement for the trigger
        $triggerSql = "CREATE TRIGGER update_total_costs_trigger AFTER INSERT ON repair_job_sheets_lines FOR EACH ROW
                        BEGIN
                            DECLARE
                                productCost DECIMAL ( 22, 4 );

                            SET productCost = NEW.quantity * NEW.unit_price;
                            UPDATE repair_job_sheets
                            SET total_costs = COALESCE ( total_costs, 0 ) + COALESCE ( service_costs, 0 ) + productCost
                            WHERE
                                id = NEW.job_sheets_id;
                        END;";

        // Execute the trigger SQL
        DB::unprepared($triggerSql);
    }

    public function dropProcedure()
    {
        // Define the function name
        $functionName = 'update_total_costs';

        // Drop the function
        DB::unprepared("DROP PROCEDURE IF EXISTS {$functionName}");
    }

    public function dropTrigger()
    {
        // Define the trigger name
        $triggerName = 'update_total_costs_trigger';

        // Drop the trigger
        DB::unprepared("DROP TRIGGER IF EXISTS {$triggerName}");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropProcedure();

        Schema::dropIfExists('repair_job_sheets_lines');

        Schema::table('repair_job_sheets', function (Blueprint $table) {
            $table->dropColumn('total_costs');
            $table->dropColumn('service_costs');
        });
    }
}
