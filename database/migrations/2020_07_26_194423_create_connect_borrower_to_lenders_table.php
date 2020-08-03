<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConnectBorrowerToLendersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('connect_borrower_to_lenders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('borrower_id');
            $table->unsignedBigInteger('lender_id');
            $table->unsignedBigInteger('sure_vault_id');
            $table->unsignedBigInteger('borrower_request_id');
            $table->string('status')->default('pending');

            $table->foreign('borrower_id')
            ->references('id')
            ->on('users');

            $table->foreign('lender_id')
            ->references('id')
            ->on('users');

            $table->foreign('sure_vault_id')
            ->references('id')
            ->on('sure_vaults');

            $table->foreign('borrower_request_id')
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
        Schema::dropIfExists('connect_borrower_to_lenders');
    }
}
