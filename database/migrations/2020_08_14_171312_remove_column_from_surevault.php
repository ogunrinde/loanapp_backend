<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnFromSurevault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sure_vaults', function (Blueprint $table) {
            Schema::table('sure_vaults', function (Blueprint $table) {
                 $table->dropForeign(['city_id']);
                $table->dropColumn('city_id');
            });
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
