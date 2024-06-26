<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToLeadHistoryTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('lead_history', function (Blueprint $table) {
            Schema::table('lead_history', function (Blueprint $table) {
                $table->index('followup_status_id');
            });

            // Add index on latest_status_id
            Schema::table('lead_history', function (Blueprint $table) {
                $table->index('latest_status_id');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('lead_history', function (Blueprint $table) {
            $table->dropIndex('followup_status_id');
            $table->dropIndex('latest_status_id');
        });
    }

}
