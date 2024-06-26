<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInputTypePikeListInDealLeadViewSetps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deal_lead_view_setps', function (Blueprint $table) {
            $table->integer('input_type')->default(1)->index()->after('order');
            $table->integer('pick_list_type')->default(1)->index()->after('order');
            $table->integer('pick_list_content_model')->default(1)->index()->after('order');
            $table->text('pick_list_content')->nullable()->after('order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deal_lead_view_setps', function (Blueprint $table) {
            //
        });
    }
}
