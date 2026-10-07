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
        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->string('reference_no')->unique();
            $table->unsignedBigInteger('amount'); // In PKR
            $table->string('payment_method'); // easypaisa, jazzcash, bank_transfer, sadapay, nayapay
            $table->string('account_title');
            $table->string('account_number');
            $table->string('bank_name')->nullable();
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('pending');
            $table->text('seller_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->string('transaction_proof')->nullable(); // Uploaded transfer receipt
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_requests');
    }
};
