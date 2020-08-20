<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOverduesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('overdues', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('paymentschedule_id');
            $table->unsignedBigInteger('borrower_id');
            $table->unsignedBigInteger('lender_id');
            $table->unsignedBigInteger('borrower_request_id');
            $table->date('ScheduledPaymentdate');
            $table->string('status');
            
            $table->foreign('borrower_request_id')
            ->references('id')
            ->on('make_requests');

             $table->foreign('lender_id')
            ->references('id')
            ->on('users');

             $table->foreign('borrower_id')
            ->references('id')
            ->on('users');

             $table->foreign('paymentschedule_id')
            ->references('id')
            ->on('payment_schedules');
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
        Schema::dropIfExists('overdues');
    }
}
