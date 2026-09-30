<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostels', function (Blueprint $table) {
            $table->id();
            $table->string('hostel_code', 50)->unique();
            $table->string('hostel_name');
            $table->string('hostel_type', 20)->default('male');
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('status', 20)->default('active');

            $table->string('biometric_device_id', 100)->nullable();
            $table->string('biometric_device_name')->nullable();
            $table->string('biometric_ip_address', 50)->nullable();
            $table->integer('biometric_port')->nullable();
            $table->string('biometric_location_code', 50)->nullable();
            $table->string('employee_code_prefix', 20)->nullable();

            $table->string('upi_id', 100)->nullable();
            $table->string('upi_payee_name')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('hostel_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostels');
    }
};
