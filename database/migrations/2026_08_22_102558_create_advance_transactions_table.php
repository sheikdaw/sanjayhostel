<?php
// database/migrations/2026_08_22_000003_create_advance_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('advance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('deducted_amount', 10, 2)->default(0);
            $table->enum('transaction_type', ['advance', 'deduction']);
            $table->date('transaction_date');
            $table->string('month', 7)->nullable(); // YYYY-MM format
            $table->string('remarks', 255)->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'month']);
            $table->index(['employee_id', 'transaction_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('advance_transactions');
    }
};