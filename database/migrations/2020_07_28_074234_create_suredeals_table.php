<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateSuredealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suredeals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lender_borrower_connection_id');
            $table->boolean('Is_cash_disbursed')->default(false);
            $table->string('date_disbursed')->nullable();
            $table->string('mode_of_disbursement')->nullable();
            $table->integer('Amount_disbursed')->nullable();
            $table->string('loanID');
            $table->boolean('Borrower_confirmed_payment')->default(false);
            $table->integer('Amount_received_from_lender')->nullable();
            $table->string('evidence_of_payment_image')->nullable();
            $table->unsignedBigInteger('lender_id');
            $table->unsignedBigInteger('borrower_id');
            $table->unsignedBigInteger('request_id');
            $table->foreign('lender_borrower_connection_id')
            ->references('id')
            ->on('connect_borrower_to_lenders');

            $table->foreign('borrower_id')
            ->references('id')
            ->on('users');

            $table->foreign('lender_id')
            ->references('id')
            ->on('users');

            $table->foreign('request_id')
            ->references('id')
            ->on('make_requests');
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
        Schema::dropIfExists('suredeals');
    }
}

