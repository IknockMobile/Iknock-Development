<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInputTypeInFollowUpLeadViewSetps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('follow_up_lead_view_setps', function (Blueprint $table) {
            $table->integer('input_type')->default(1)->index()->after('order_no');
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
            $table->dropColumn('input_type');
        });
    }
}
