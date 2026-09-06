<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->text('remark')->nullable()->after('transaction_id');
            $table->string('payment_type')->nullable()->after('remark');
            $table->decimal('previous_pending_cleared', 10, 2)->default(0)->after('payment_type');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['remark', 'payment_type', 'previous_pending_cleared']);
        });
    }
};