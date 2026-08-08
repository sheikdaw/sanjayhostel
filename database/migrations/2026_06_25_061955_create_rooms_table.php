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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();

            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();

            $table->string('room_no');
            $table->string('normol_cot_count');
            $table->string('bunker_cot_count');

            $table->enum('status', [
                'VACANT',
                'PARTIAL',
                'FULL',
                'MAINTENANCE'
            ])->default('VACANT');

            $table->timestamps();

            $table->unique(['hostel_id', 'room_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
