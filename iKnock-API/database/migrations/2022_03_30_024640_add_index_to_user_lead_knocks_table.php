<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToUserLeadKnocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_lead_knocks', function (Blueprint $table) {
            $table->index(['id','lead_id','user_id','status_id']);
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
            $table->dropIndex(['id','lead_id','user_id','status_id']);
        });
    }
}
