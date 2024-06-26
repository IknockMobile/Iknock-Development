<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChangesDatatypeLoan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   

        Schema::table('lead_detail', function (Blueprint $table) {
            DB::statement("ALTER TABLE `lead_detail` CHANGE `original_loan` `original_loan` BIGINT NULL DEFAULT '0';");
            DB::statement("ALTER TABLE `lead_detail` CHANGE `sq_ft` `sq_ft` BIGINT NULL DEFAULT NULL;");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_detail', function (Blueprint $table) {
            //
        });
    }
}
