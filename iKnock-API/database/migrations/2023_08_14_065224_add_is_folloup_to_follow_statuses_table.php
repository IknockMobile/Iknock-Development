<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsFolloupToFollowStatusesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('follow_statuses', function (Blueprint $table) {
            $table->tinyInteger('is_followup')->default(1);
            $table->tinyInteger('is_purchase')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('follow_statuses', function (Blueprint $table) {
            $table->dropColumn('is_followup');
            $table->dropColumn('is_purchase');
        });
    }

}
