<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsShowInFollowUpLeadViewSetps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('follow_up_lead_view_setps', function (Blueprint $table) {
            $table->tinyInteger('is_show')->default(1)->after('title_slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('follow_up_lead_view_setps', function (Blueprint $table) {
            $table->dropColumn('is_show');
        });
    }
}
