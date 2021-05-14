<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Changesuredealsdatatype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suredeals', function (Blueprint $table) {
            $table->string('PaymentStatus')->nullable()->change();
            $table->string('date_borrower_withdraw')->nullable()->change();
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
