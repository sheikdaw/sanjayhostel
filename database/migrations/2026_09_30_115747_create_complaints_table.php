<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();

            $table->string('complaint_number', 50)->unique();

            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('room_number', 20)->nullable();

            $table->string('category', 30);
            $table->string('priority', 20)->default('medium');
            $table->text('description');
            $table->string('image')->nullable();

            $table->string('status', 20)->default('pending');
            $table->text('admin_remark')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['hostel_id', 'resident_id']);
            $table->index(['hostel_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
