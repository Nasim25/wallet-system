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
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
                $table->string('trx_id', 100)->unique();
                $table->string('payment_id', 100)->nullable();
                $table->enum('type', ['credit', 'debit', 'refund']);
                $table->decimal('amount', 15, 2);
                $table->decimal('balance_after', 15, 2);
                $table->enum('status', ['pending', 'completed', 'failed', 'cancelled']);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
