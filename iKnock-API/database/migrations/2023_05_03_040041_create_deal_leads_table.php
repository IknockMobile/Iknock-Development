<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deal_leads', function (Blueprint $table) {
            $table->id();
            $table->integer('lead_id')->nullable();
            $table->string('title')->nullable();
            $table->string('owner')->nullable();
            $table->string('address')->nullable();
            $table->string('formatted_address')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('sq_ft')->nullable();
            $table->string('yr_blt')->nullable();
            $table->integer('deal_status')->nullable();
            $table->integer('investor_id')->nullable();
            $table->integer('closer_id')->nullable();
            $table->integer('deal_type')->nullable();
            $table->integer('purchase_finance')->nullable();
            $table->integer('ownership')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('sell_date')->nullable();
            $table->decimal('purchase_price',14,2)->nullable();
            $table->decimal('purchase_closing_costs',14,2)->nullable();
            $table->decimal('cash_in_at_purchase',14,2)->nullable();
            $table->decimal('rehab_and_other_costs',14,2)->nullable();
            $table->decimal('total_cash_in',14,2)->nullable();
            $table->decimal('Investor_commission',14,2)->nullable();
            $table->decimal('total_cost',14,2)->nullable();
            $table->decimal('sales_value',14,2)->nullable();
            $table->decimal('sales_cash_proceeds',14,2)->nullable();
            $table->decimal('lh_profit_after_sharing',14,2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deal_leads');
    }
}
