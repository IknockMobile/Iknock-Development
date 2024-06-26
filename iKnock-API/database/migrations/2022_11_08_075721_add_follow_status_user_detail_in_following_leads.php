<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFollowStatusUserDetailInFollowingLeads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('following_leads', function (Blueprint $table) {
            $table->integer('follow_status')->nullable();
            $table->integer('user_detail')->nullable();
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
            //
        });
    }
}
