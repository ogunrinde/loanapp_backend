<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type');
            $table->unsignedBigInteger('role_id');
            $table->boolean('create')->default(0);
            $table->boolean('edit')->default(0);
            $table->boolean('approval')->default(0);
            $table->boolean('deactivate')->default(0);
            $table->boolean('view')->default(0);
            $table->boolean('cancel')->default(0);
            $table->foreign('role_id')
            ->references('id')
            ->on('roles')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}
