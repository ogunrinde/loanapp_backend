<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaultWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vault_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sure_vault_id');
            $table->integer('Amount_withdrawn');
            $table->unsignedBigInteger('make_request_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('make_request_id')
            ->references('id')
            ->on('make_requests');

            $table->foreign('user_id')
            ->references('id')
            ->on('users');

             $table->foreign('sure_vault_id')
            ->references('id')
            ->on('sure_vaults');

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
        Schema::dropIfExists('vault_withdrawals');
    }
}
