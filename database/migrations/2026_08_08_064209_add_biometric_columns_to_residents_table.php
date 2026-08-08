<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            // Add biometric columns if they don't exist
            if (!Schema::hasColumn('residents', 'employee_code')) {
                $table->string('employee_code')->nullable()->unique()->after('id');
            }
            
            if (!Schema::hasColumn('residents', 'biometric_access')) {
                $table->boolean('biometric_access')->default(true)->after('employee_code');
            }
            
            if (!Schema::hasColumn('residents', 'last_sync_at')) {
                $table->timestamp('last_sync_at')->nullable()->after('biometric_access');
            }
            
            if (!Schema::hasColumn('residents', 'access_disabled_at')) {
                $table->timestamp('access_disabled_at')->nullable()->after('last_sync_at');
            }
            
            if (!Schema::hasColumn('residents', 'access_enabled_at')) {
                $table->timestamp('access_enabled_at')->nullable()->after('access_disabled_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropColumn([
                'employee_code',
                'biometric_access',
                'last_sync_at',
                'access_disabled_at',
                'access_enabled_at'
            ]);
        });
    }
};