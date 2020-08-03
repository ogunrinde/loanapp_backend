<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoanTenorToSurevaultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sure_vaults', function (Blueprint $table) {
            $table->integer('maxloantenor');
            $table->integer('minloantenor');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sure_vaults', function (Blueprint $table) {
            //
        });
    }
}
