<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ColumnsDataType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('make_requests', function (Blueprint $table) {
            $table->decimal('maxInterestRate', 8, 2)->change();
            $table->decimal('minInterestRate', 4, 2)->change();
            $table->decimal('requestAmount',12,2)->change();
        });

        Schema::table('sure_vaults', function (Blueprint $table) {
            $table->decimal('maxRequestAmount', 12, 2)->change();
            $table->decimal('minRequestAmount', 12, 2)->change();
            $table->decimal('minInterestperMonth',4,2)->change();
            $table->decimal('maxInterestperMonth',4,2)->change();
        });

        Schema::table('suredeals', function (Blueprint $table) {
            $table->decimal('Amount_disbursed', 12, 2)->change();
        });

        Schema::table('vault_withdrawals', function (Blueprint $table) {
            $table->decimal('Amount_withdrawn', 12, 2)->change();
        });

        Schema::table('repayments', function (Blueprint $table) {
            $table->decimal('amount_paid', 12, 2)->change();
        });

        Schema::table('payment_schedules', function (Blueprint $table) {
            $table->decimal('expected_amount_to_paid', 12, 2)->change();
        });

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('make_requests', function (Blueprint $table) {
            //
        });
    }
}
