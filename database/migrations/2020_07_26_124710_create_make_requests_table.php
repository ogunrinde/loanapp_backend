<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMakeRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('make_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('requestAmount');
            $table->string('loanperiod');
            $table->integer('maxInterestRate');
            $table->integer('minInterestRate');
            $table->string('repaymentplan');
            $table->string('requiredcreditBereau');
            $table->boolean('requestStatus')->default(false);
            $table->unsignedBigInteger('lender_country_id');
            $table->unsignedBigInteger('lender_state_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('user_id')
            ->references('id')
            ->on('users');

            $table->foreign('lender_country_id')
            ->references('id')
            ->on('countries');

            $table->foreign('lender_state_id')
            ->references('id')
            ->on('states');

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
        Schema::dropIfExists('make_requests');
    }
}
