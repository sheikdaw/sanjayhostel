<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bed_id')->constrained()->cascadeOnDelete();

            // Biometric
            $table->string('employee_code', 50)->nullable()->unique();
            $table->boolean('biometric_access')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('access_enabled_at')->nullable();
            $table->timestamp('access_disabled_at')->nullable();

            // Basic
            $table->string('resident_code', 50)->unique();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('parentsphone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('aadhaar_no', 20)->nullable();
            $table->text('address')->nullable();
            $table->date('dob');

            // Documents
            $table->string('profile_image')->nullable();
            $table->string('aadhar_document')->nullable();
            $table->string('application_document')->nullable();

            // Stay
            $table->date('joining_date');
            $table->date('vacate_date')->nullable();
            $table->string('food_status', 20)->default('WITH_FOOD');
            $table->decimal('rent_amount', 10, 2)->default(0);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->string('status', 20)->default('ACTIVE');

            $table->timestamps();

            $table->index('status');
            $table->index(['hostel_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
