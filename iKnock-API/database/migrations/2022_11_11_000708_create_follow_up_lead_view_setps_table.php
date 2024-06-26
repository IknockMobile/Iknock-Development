<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowUpLeadViewSetpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('follow_up_lead_view_setps', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('title_slug')->nullable();
            $table->string('view_type')->default(1);
            $table->integer('order_no')->default(1);
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
        Schema::dropIfExists('follow_up_lead_view_setps');
    }
}
