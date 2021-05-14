<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Updatesuredealtable4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suredeals', function (Blueprint $table) {
            $table->string('borrowertransfermessage')->nullable();
            $table->mediumText('borrowertransferInformation')->nullable();
            $table->mediumText('finalborrowertransferInformation')->nullable();
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
