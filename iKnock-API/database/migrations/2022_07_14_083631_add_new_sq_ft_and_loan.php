<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewSqFtAndLoan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_detail', function (Blueprint $table) {
            $table->bigInteger('sq_ft_2')->nullable();
            $table->bigInteger('original_loan_2')->nullable();
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
            $table->dropIndex('sq_ft_2');
            $table->dropIndex('original_loan_2');
        });
    }
}
