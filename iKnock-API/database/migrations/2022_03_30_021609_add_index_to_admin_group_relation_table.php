<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToAdminGroupRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_group_relation', function (Blueprint $table) {
            $table->index(['id', 'admin_id', 'admin_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_group_relation', function (Blueprint $table) {
            $table->dropIndex(['id', 'admin_id', 'admin_group_id']);            
        });
    }
}
