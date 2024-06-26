<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDashboardlistToPaginationlimitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('paginationlimit', function (Blueprint $table) {
            $table->integer('purchase_conversion_rate')->nullable();
            $table->integer('contract_conversion_rate')->nullable();
            $table->integer('appointments_requested_conversion_rate')->nullable();
            $table->integer('appointments_kept_conversion_rate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('paginationlimit', function (Blueprint $table) {
            //
        });
    }
}
