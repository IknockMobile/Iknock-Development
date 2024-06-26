<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowingLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('following_leads', function (Blueprint $table) {
            $table->id();
            $table->integer('lead_id')->nullable();
            $table->string('title')->nullable();
            $table->string('owner')->nullable();
            $table->string('address')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('foreclosure_date')->nullable();
            $table->string('identifier')->nullable();
            $table->string('formatted_address')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->integer('type_id')->nullable();
            $table->integer('status_id')->nullable();
            $table->tinyInteger('is_verified')->default(0);
            $table->integer('creator_id')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('assignee_id')->nullable();
            $table->tinyInteger('is_expired')->default(0);
            $table->decimal('latitude', 10,8)->nullable(); 
            $table->decimal('longitude', 11,8)->nullable(); 
            $table->dateTime('appointment_date')->nullable();
            $table->binary('appointment_result')->nullable();
            $table->string('auction')->nullable();
            $table->string('lead_value')->nullable();
            $table->string('original_loan')->nullable();
            $table->string('loan_date')->nullable();
            $table->string('sq_ft')->nullable();
            $table->string('yr_blt')->nullable();
            $table->string('eq')->nullable();
            $table->string('mortgagee')->nullable();
            $table->string('loan_type')->nullable();
            $table->string('loan_mod')->nullable();
            $table->string('trustee')->nullable();
            $table->string('owner_address')->nullable();
            $table->string('source')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->bigInteger('sq_ft_2')->nullable();
            $table->bigInteger('original_loan_2')->nullable();
            $table->longtext('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('following_leads');
    }
}
