<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOrignalDateLeadDetailTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement("ALTER TABLE `lead_detail` CHANGE `original_loan` `original_loan` VARCHAR(255) NULL DEFAULT '';");
        DB::statement("ALTER TABLE `lead_detail` CHANGE `sq_ft` `sq_ft` VARCHAR(255) NULL DEFAULT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }

}
