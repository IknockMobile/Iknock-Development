<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaginationlimitTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('paginationlimit', function (Blueprint $table) {
            $table->id();
            $table->integer('lead_management')->nullable();
            $table->integer('followup_lead_management')->nullable();
            $table->integer('purchase_lead_management')->nullable();
            $table->integer('deal_management')->nullable();
            $table->integer('marketing_lead_management')->nullable();
            $table->integer('knock_list')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('paginationlimit');
    }

}
