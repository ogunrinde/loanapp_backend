<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Updatetablevaultwithdrawal2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vault_withdrawals', function (Blueprint $table) {
            $table->mediumText('transferInformation')->nullable();
            $table->mediumText('finaltransferInformation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vault_withdrawals', function (Blueprint $table) {
            //
        });
    }
}
