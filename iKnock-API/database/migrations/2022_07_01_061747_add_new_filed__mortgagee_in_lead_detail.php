<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFiledMortgageeInLeadDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_detail', function (Blueprint $table) {
            $table->string('mortgagee')->nullable();
            $table->string('loan_type')->nullable();
            $table->string('loan_mod')->nullable();
            $table->string('trustee')->nullable();
            $table->text('owner_address')->nullable();
            $table->string('source')->nullable();
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
            $table->dropColumn('mortgagee');
            $table->dropColumn('loan_type');
            $table->dropColumn('loan_mod');
            $table->dropColumn('trustee');
            $table->dropColumn('owner_address');
            $table->dropColumn('source');
        });
    }
}
