<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChangeDefaultStatusMarketingCampaigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
               \DB::statement("ALTER TABLE `marketing_campaigns` CHANGE `status` `status` TINYINT NOT NULL DEFAULT '0'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            //
        });
    }
}
