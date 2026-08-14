<?php
// database/migrations/2024_01_15_add_upi_id_to_hostels.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('hostels', function (Blueprint $table) {
            $table->string('upi_id')->nullable()->after('employee_code_prefix')
                ->comment('UPI ID for receiving payments (e.g., merchant@upi)');
            $table->string('upi_payee_name')->nullable()->after('upi_id')
                ->comment('Payee name shown in UPI app');
        });
    }

    public function down()
    {
        Schema::table('hostels', function (Blueprint $table) {
            $table->dropColumn(['upi_id', 'upi_payee_name']);
        });
    }
};
