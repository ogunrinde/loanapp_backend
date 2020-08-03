<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repayments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('borrower_id');
            $table->unsignedBigInteger('lender_id');
            $table->unsignedBigInteger('borrower_request_id');
            $table->integer('amount_paid')->nullable();
            $table->dateTime('date_paid')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->string('mode_of_payment')->nullable();
            $table->string('remarks')->nullable();

            $table->foreign('borrower_id')
            ->references('id')
            ->on('users');

            $table->foreign('lender_id')
            ->references('id')
            ->on('users');

            $table->foreign('borrower_request_id')
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
        Schema::dropIfExists('repayments');
    }
}
