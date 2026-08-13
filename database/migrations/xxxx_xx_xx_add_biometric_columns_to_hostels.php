<?php
// database/migrations/xxxx_xx_xx_add_biometric_columns_to_hostels.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hostels', function (Blueprint $table) {
            // Biometric Device Configuration
            if (!Schema::hasColumn('hostels', 'biometric_device_id')) {
                $table->string('biometric_device_id')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('hostels', 'biometric_device_name')) {
                $table->string('biometric_device_name')->nullable()->after('biometric_device_id');
            }
            
            if (!Schema::hasColumn('hostels', 'biometric_ip_address')) {
                $table->string('biometric_ip_address')->nullable()->after('biometric_device_name');
            }
            
            if (!Schema::hasColumn('hostels', 'biometric_port')) {
                $table->string('biometric_port')->default('4370')->after('biometric_ip_address');
            }
            
            if (!Schema::hasColumn('hostels', 'biometric_location_code')) {
                $table->string('biometric_location_code')->nullable()->after('biometric_port');
            }
            
            if (!Schema::hasColumn('hostels', 'employee_code_prefix')) {
                $table->string('employee_code_prefix')->nullable()->after('biometric_location_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hostels', function (Blueprint $table) {
            $columns = [
                'biometric_device_id',
                'biometric_device_name',
                'biometric_ip_address',
                'biometric_port',
                'biometric_location_code',
                'employee_code_prefix'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('hostels', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};