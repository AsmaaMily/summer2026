<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('recurring_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('amount');
            $table->string('type', 20);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->string('payment_method', 20);
            $table->boolean('is_recurring')->default(false);
            $table->string('source', 20)->default('manual');
            $table->boolean('is_flagged_duplicate')->default(false);
            $table->string('flag_reason', 50)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'category_id', 'date']);
            $table->index(['user_id', 'payment_method', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
