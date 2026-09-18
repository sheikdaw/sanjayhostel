<?php
// database/migrations/xxxx_create_complaints_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained()->onDelete('cascade');

            // Resident MUST exist — so NOT NULL
            $table->foreignId('resident_id')->constrained()->onDelete('cascade');

            $table->string('complaint_number')->unique();

            // Snapshot values (complaint time-la iruntha data)
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('room_number')->nullable();

            $table->enum('category', [
                'electrical', 'plumbing', 'furniture',
                'cleaning', 'wifi', 'food', 'security', 'other'
            ]);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->text('description');
            $table->string('image')->nullable(); // photo path

            $table->enum('status', ['pending', 'in_progress', 'resolved', 'rejected'])
                  ->default('pending');
            $table->text('admin_remark')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['hostel_id', 'resident_id']);
            $table->index(['hostel_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
