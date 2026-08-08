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
            // Profile image
            $table->string('profile_image')->nullable()->after('address');

            // Aadhar document
            $table->string('aadhar_document')->nullable()->after('aadhaar_no');

            // Application document
            $table->string('application_document')->nullable()->after('aadhar_document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropColumn(['profile_image', 'aadhar_document', 'application_document']);
        });
    }
};
