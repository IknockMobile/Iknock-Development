<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPickListType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('follow_up_lead_view_setps', function (Blueprint $table) {
            $table->integer('pick_list_type')->default(1)->index()->after('order_no');
            $table->integer('pick_list_content_model')->default(1)->index()->after('order_no');
            $table->text('pick_list_content')->nullable()->after('order_no');
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
            $table->dropColumn('pick_list_type');
            $table->dropColumn('pick_list_content');
            $table->dropColumn('pick_list_content_model');
        });

    }
}
