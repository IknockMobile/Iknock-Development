<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressInMarketings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketings', function (Blueprint $table) {
            $table->text('m_street_address')->nullable()->after('custom_fields');
            $table->text('m_street_address_2')->nullable()->after('custom_fields');
            $table->string('m_city')->nullable()->after('custom_fields');
            $table->string('m_state')->nullable()->after('custom_fields');
            $table->string('m_zip')->nullable()->after('custom_fields');
            $table->string('m_country')->nullable()->after('custom_fields');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketings', function (Blueprint $table) {
            $table->dropColumn('m_street_address');
            $table->dropColumn('m_street_address_2');
            $table->dropColumn('m_city');
            $table->dropColumn('m_state');
            $table->dropColumn('m_zip');
            $table->dropColumn('m_country');
        });
    }
}
