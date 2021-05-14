<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBankdetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_information', function (Blueprint $table) {
            $table->string('bankcode')->nullable();
            if(!Schema::hasColumn('bank_information','Is_BVN_verified'))
            $table->boolean('Is_BVN_verified')->default(0);
            if(!Schema::hasColumn('bank_information','Is_AccountNumber_verified'))
            $table->boolean('Is_AccountNumber_verified')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_information', function (Blueprint $table) {
            //
        });
    }
}
