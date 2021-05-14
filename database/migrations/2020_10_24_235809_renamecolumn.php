<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Renamecolumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('make_requests', function (Blueprint $table) {
             $table->renameColumn('lender_country_id','borrower_country_id');
             $table->renameColumn('lender_state_id','borrower_state_id');
             $table->renameColumn('lender_city_id','borrower_city_id');

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
