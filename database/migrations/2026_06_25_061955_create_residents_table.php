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
        Schema::create('residents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();

            $table->foreignId('room_id')->constrained()->cascadeOnDelete();

            $table->foreignId('bed_id')->constrained()->cascadeOnDelete();

            $table->string('resident_code')->unique();

            $table->string('name');

            $table->string('phone');
            $table->string('parentsphone')->nullable();

            $table->string('email')->nullable();

            $table->string('aadhaar_no')->nullable();

            $table->text('address')->nullable();

            $table->date('joining_date');
            $table->date('dob');
$table->enum('food_status', ['WITH_FOOD', 'WITHOUT_FOOD'])->default('WITH_FOOD');
            $table->date('vacate_date')->nullable();
 $table->decimal('rent_amount', 10, 2)->default(0);
            $table->decimal('deposit_amount', 10, 2)->default(0);

            $table->enum('status', [
                'ACTIVE',
                'VACATED'
            ])->default('ACTIVE');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
