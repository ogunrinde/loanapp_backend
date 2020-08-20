<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('borrower_id');
            $table->unsignedBigInteger('lender_id');
            $table->unsignedBigInteger('borrower_request_id');
            $table->integer('expected_amount_to_paid');
            $table->date('dueDate');
            $table->boolean('Is_borrower_notified')->default(false);
            $table->boolean('Is_lender_notified')->default(false);
            $table->string('status');

            $table->foreign('borrower_request_id')
            ->references('id')
            ->on('make_requests');

            $table->foreign('borrower_id')
            ->references('id')
            ->on('users');

            $table->foreign('lender_id')
            ->references('id')
            ->on('users');

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
        Schema::dropIfExists('payment_schedules');
    }
}
