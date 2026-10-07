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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_channel')->nullable()->after('method');
            $table->string('sender_name')->nullable()->after('payment_channel');
            $table->string('sender_account')->nullable()->after('sender_name');
            $table->string('transaction_id')->nullable()->index()->after('sender_account');
            $table->string('proof_image')->nullable()->after('transaction_id');
            $table->text('admin_notes')->nullable()->after('status');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()->after('admin_notes');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
        });

        Schema::table('order_groups', function (Blueprint $table) {
            $table->string('payment_method', 50)->default('cod')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'payment_channel',
                'sender_name',
                'sender_account',
                'transaction_id',
                'proof_image',
                'admin_notes',
                'verified_by',
                'verified_at',
            ]);
        });
    }
};
