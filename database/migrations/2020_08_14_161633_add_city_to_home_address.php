<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCityToHomeAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_home_addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id');

            $table->foreign('city_id')
            ->references('id')
            ->on('cities');
        });

        Schema::table('user_office_addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id');

            $table->foreign('city_id')
            ->references('id')
            ->on('cities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_home_address', function (Blueprint $table) {
            //
        });
    }
}
