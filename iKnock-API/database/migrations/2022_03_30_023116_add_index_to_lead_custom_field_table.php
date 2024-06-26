<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToLeadCustomFieldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_custom_field', function (Blueprint $table) {
            $table->index(['id','lead_id','tenant_custom_field_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_custom_field', function (Blueprint $table) {
            $table->dropIndex(['id','lead_id','tenant_custom_field_id']);
        });
    }
}
