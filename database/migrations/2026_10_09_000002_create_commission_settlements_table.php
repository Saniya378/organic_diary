<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->string('payment_channel'); // easypaisa, jazzcash, bank_transfer
            $table->string('transaction_id');
            $table->string('proof_image')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('seller_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_settlements');
    }
};
