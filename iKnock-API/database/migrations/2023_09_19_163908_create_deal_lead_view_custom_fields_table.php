<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealLeadViewCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deal_lead_view_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('deal_lead_id')->nullable();
            $table->bigInteger('deal_view_id')->nullable();
            $table->text('field_value')->nullable();
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
        Schema::dropIfExists('deal_lead_view_custom_fields');
    }
}
