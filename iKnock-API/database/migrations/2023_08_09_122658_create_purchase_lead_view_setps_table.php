<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseLeadViewSetpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_lead_view_setps', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('title_slug')->nullable();
            $table->string('view_type')->default(1);
            $table->integer('order_no')->default(1);
            $table->tinyInteger('is_show')->default(1);
            $table->integer('input_type')->default(1)->index();
            $table->integer('pick_list_type')->default(1)->index();
            $table->integer('pick_list_content_model')->default(1)->index();
            $table->text('pick_list_content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_lead_view_setps');
    }
}
