<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdatedByFieldsToFollowingLeadsName extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('following_leads', function (Blueprint $table) {
            $table->integer('purchase_date_updated_by')->nullable();
            $table->integer('contract_date_updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('following_leads', function (Blueprint $table) {
            $table->dropColumn('purchase_date_updated_by');
            $table->dropColumn('contract_date_updated_by');
        });
    }

}
