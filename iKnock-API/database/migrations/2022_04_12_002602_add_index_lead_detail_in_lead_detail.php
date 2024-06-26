<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexLeadDetailInLeadDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_detail', function (Blueprint $table) {
            $table->index(['is_expired','appointment_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_detail', function (Blueprint $table) {
            $table->dropIndex(['is_expired','appointment_date']);
        });
    }
}
