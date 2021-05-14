<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDelete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('make_requests', function (Blueprint $table) {
            if(!Schema::hasColumn('make_requests','deleted_at'))
             $table->softDeletes();
        });

        Schema::table('sure_vaults', function (Blueprint $table) {
            if(!Schema::hasColumn('sure_vaults','deleted_at'))
             $table->softDeletes();
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
