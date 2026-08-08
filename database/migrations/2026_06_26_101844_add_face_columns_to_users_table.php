<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('face_id')->nullable()->unique()->after('profile');
            $table->boolean('face_registered')->default(false)->after('face_id');
            $table->timestamp('face_registered_at')->nullable()->after('face_registered');
            $table->string('face_image_path')->nullable()->after('face_registered_at');
            $table->json('face_encoding')->nullable()->after('face_image_path'); // Optional: store encoding
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'face_id',
                'face_registered',
                'face_registered_at',
                'face_image_path',
                'face_encoding'
            ]);
        });
    }
};
