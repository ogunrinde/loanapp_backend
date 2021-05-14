<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Changesurevaulttableinfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sure_vaults', function (Blueprint $table) {
             $table->unsignedBigInteger('borrower_country_id')->nullable()->change();
             $table->unsignedBigInteger('borrower_state_id')->nullable()->change();
             //$table->unsignedBigInteger('borrower_city_id')->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
