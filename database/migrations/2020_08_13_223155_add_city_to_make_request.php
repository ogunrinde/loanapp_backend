<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCityToMakeRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('make_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('lender_city_id');

            $table->foreign('lender_city_id')
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
        Schema::table('make_requests', function (Blueprint $table) {
            //
        });
    }
}
