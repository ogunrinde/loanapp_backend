<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Updatetablesurevault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sure_vaults', function (Blueprint $table) {
            $table->boolean('creditbureau_report_required')->default(0)->after('bvn_must_be_verified');
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
