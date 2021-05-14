<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Updatesuredealtable3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suredeals', function (Blueprint $table) {
            $table->boolean('Has_borrower_withdraw_cash')->default(false);
            $table->date('date_borrower_withdraw');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suredeals', function (Blueprint $table) {
            //
        });
    }
}
