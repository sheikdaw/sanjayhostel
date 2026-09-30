<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('room_no');
            $table->integer('normal_cot_count')->default(0);
            $table->integer('bunker_cot_count')->default(0);
            $table->string('status', 20)->default('VACANT');
            $table->timestamps();

            $table->unique(['hostel_id', 'room_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
