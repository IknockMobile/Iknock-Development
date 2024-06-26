<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealLeadViewSetpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deal_lead_view_setps', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('title_slug')->nullable();
            $table->tinyInteger('is_show')->default(1);
            $table->tinyInteger('view_type')->default(1);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deal_lead_view_setps');
    }
}
