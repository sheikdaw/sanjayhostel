<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('bed_no');
            $table->string('bed_type', 20)->default('NORMAL');
            $table->string('status', 20)->default('VACANT');
            $table->timestamps();

            $table->unique(['room_id', 'bed_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
