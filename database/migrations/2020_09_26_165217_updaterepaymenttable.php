<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Updaterepaymenttable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('repayments', function (Blueprint $table) {
            $table->mediumText('transferInformation')->nullable();
            $table->string('PaymentStatus')->nullable();
            $table->boolean('IsWithdrawn')->default(false);
            $table->string('WithdrawnType')->nullable();
            $table->date('DateWithdrawn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('repayments', function (Blueprint $table) {
            //
        });
    }
}
