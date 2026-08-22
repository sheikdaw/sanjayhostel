<?php
// database/migrations/2026_08_22_000001_create_employees_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->nullable()->constrained('hostels')->onDelete('set null');
            $table->string('employee_code', 50)->unique();
            $table->string('name', 150);
            $table->string('department', 100)->nullable();
            $table->string('designation', 100)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->date('joining_date')->nullable();
            $table->decimal('salary', 10, 2)->default(0);
            $table->decimal('advance_amount', 10, 2)->default(0);
            $table->decimal('advance_deduct', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};