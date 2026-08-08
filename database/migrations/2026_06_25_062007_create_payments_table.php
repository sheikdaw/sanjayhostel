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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();

            $table->string('receipt_no')->unique();

            $table->integer('month');

            $table->integer('year');

            $table->decimal('rent_amount', 10, 2);

            $table->decimal('discount_amount', 10, 2)->default(0);

            $table->decimal('fine_amount', 10, 2)->default(0);

            $table->decimal('cash_paid_amount', 10, 2);
             $table->decimal('upi_paid_amount', 10, 2);

            $table->decimal('balance_amount', 10, 2)->default(0);

            $table->date('payment_date');
            $table->string('transaction_id')->nullable();

            $table->enum('status', [
                'PAID',
                'PARTIAL',
                'PENDING'
            ])->default('PENDING');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
