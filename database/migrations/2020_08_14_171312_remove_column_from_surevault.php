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
            if(Schema::hasColumn('sure_vaults', 'borrower_city_id')) 
            {
                $table->dropForeign(['borrower_city_id']);
                $table->dropColumn('borrower_city_id');
            }
            
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
