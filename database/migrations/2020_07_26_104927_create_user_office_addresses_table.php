<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserOfficeAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_office_addresses', function (Blueprint $table) {
            $table->id();
            $table->text('address');
            $table->string('employmentstatus');
            $table->bigInteger('contact_number')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_website')->nullable();
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('state_id');
            $table->unsignedBigInteger('user_id');

            $table->boolean('is_verified')->default(false);
            $table->foreign('country_id')
            ->references('id')
            ->on('countries');

            $table->foreign('state_id')
            ->references('id')
            ->on('states');

            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');
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
        Schema::dropIfExists('user_office_addresses');
    }
}
