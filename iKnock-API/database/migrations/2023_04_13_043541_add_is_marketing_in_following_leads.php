<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsMarketingInFollowingLeads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('following_leads', function (Blueprint $table) {
            $table->tinyInteger('is_marketing')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('following_leads', function (Blueprint $table) {
            $table->dropColumn('is_marketing');
        });
    }
}
