<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSureVaultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sure_vaults', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('fundamount');
            $table->string('availablefrom');
            $table->string('availableto');
            $table->bigInteger('maxRequestAmount');
            $table->bigInteger('minRequestAmount');
            $table->bigInteger('minInterestperMonth');
            $table->bigInteger('maxInterestperMonth');
            $table->unsignedBigInteger('borrower_country_id');
            $table->unsignedBigInteger('borrower_state_id');
            $table->boolean('email_must_be_verified')->default(false);
            $table->boolean('phonenumber_must_be_verified')->default(false);
            $table->boolean('bvn_must_be_verified')->default(false);
            $table->unsignedBigInteger('user_id');


            $table->foreign('user_id')
            ->references('id')
            ->on('users');

            $table->foreign('borrower_country_id')
            ->references('id')
            ->on('countries');

            $table->foreign('borrower_state_id')
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
        Schema::dropIfExists('sure_vaults');
    }
}
