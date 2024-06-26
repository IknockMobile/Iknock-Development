<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLatlongToUserLeadKnocksTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('user_lead_knocks', function (Blueprint $table) {
            $table->string('lead_lat')->nullable();
            $table->string('lead_long')->nullable();
            $table->string('application_lat')->nullable();
            $table->string('application_long')->nullable();
            $table->string('backend_distance')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('user_lead_knocks', function (Blueprint $table) {
            $table->dropColumn('lead_lat');
            $table->dropColumn('lead_long');
            $table->dropColumn('application_lat');
            $table->dropColumn('application_long');
            $table->dropColumn('backend_distance');
        });
    }

}
