<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsHistoricalInUserLeadKnocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_lead_knocks', function (Blueprint $table) {
            $table->tinyInteger('is_historical')->default(0);
        });

        Schema::table('lead_history', function (Blueprint $table) {
            $table->tinyInteger('is_historical')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_lead_knocks', function (Blueprint $table) {
            $table->dropColumn('is_historical');
        });

        Schema::table('lead_history', function (Blueprint $table) {
            $table->dropColumn('is_historical');
        });
    }
}
