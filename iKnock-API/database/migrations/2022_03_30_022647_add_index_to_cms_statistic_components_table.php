<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToCmsStatisticComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cms_statistic_components', function (Blueprint $table) {
             $table->index(['id','id_cms_statistics']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cms_statistic_components', function (Blueprint $table) {
            $table->dropIndex(['id','id_cms_statistics']);
        });
    }
}
