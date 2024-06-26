<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToTenantCustomFieldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenant_custom_field', function (Blueprint $table) {
           $table->index(['id','tenant_id','template_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenant_custom_field', function (Blueprint $table) {
            $table->dropIndex(['id','tenant_id','template_id']);
        });
    }
}
