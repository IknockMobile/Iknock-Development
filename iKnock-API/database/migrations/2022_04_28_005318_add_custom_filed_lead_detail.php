<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomFiledLeadDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_detail', function (Blueprint $table) {
            $table->string('auction')->index()->nullable();
            $table->string('lead_value')->index()->nullable();
            $table->string('original_loan')->index()->nullable();
            $table->string('loan_date')->index()->nullable();
            $table->string('sq_ft')->index()->nullable();
            $table->string('yr_blt')->index()->nullable();
            $table->string('eq')->index()->nullable();
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
            $table->dropColumn('auction');
            $table->dropColumn('lead_value');
            $table->dropColumn('original_loan');
            $table->dropColumn('loan_date');
            $table->dropColumn('sq_ft');
            $table->dropColumn('yr_blt');
            $table->dropColumn('eq');
        });
    }
}
