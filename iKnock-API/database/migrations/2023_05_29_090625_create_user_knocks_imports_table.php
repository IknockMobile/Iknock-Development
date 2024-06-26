<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserKnocksImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_knocks_imports', function (Blueprint $table) {
            $table->id();
            $table->string('status')->nullable();
            $table->year('year')->nullable();
            $table->integer('month')->nullable();
            $table->string('investor')->nullable();
            $table->integer('of_knocks')->nullable();
            $table->integer('appt_scheduled')->nullable();
            $table->tinyInteger('is_run')->default(0);
            $table->timestamps();
        });

        Schema::table('lead_history', function (Blueprint $table) {
            $table->integer('historical_id')->nullable();
        });

         Schema::table('user_lead_knocks', function (Blueprint $table) {
            $table->integer('historical_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_knocks_imports');

        Schema::table('user_lead_knocks', function (Blueprint $table) {
            $table->dropColumn('historical_id');
        });

        Schema::table('lead_history', function (Blueprint $table) {
            $table->dropColumn('historical_id');
        });
    }
}
