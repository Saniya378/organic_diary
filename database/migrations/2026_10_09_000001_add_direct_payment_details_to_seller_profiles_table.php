<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('description');
            $table->string('bank_account_title')->nullable()->after('bank_name');
            $table->string('bank_account_number')->nullable()->after('bank_account_title');
            $table->string('bank_iban')->nullable()->after('bank_account_number');
            $table->string('bank_branch')->nullable()->after('bank_iban');
            $table->string('easypaisa_title')->nullable()->after('bank_branch');
            $table->string('easypaisa_number')->nullable()->after('easypaisa_title');
            $table->string('jazzcash_title')->nullable()->after('easypaisa_number');
            $table->string('jazzcash_number')->nullable()->after('jazzcash_title');
            $table->text('payment_instructions')->nullable()->after('jazzcash_number');
        });
    }

    public function down(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_account_title',
                'bank_account_number',
                'bank_iban',
                'bank_branch',
                'easypaisa_title',
                'easypaisa_number',
                'jazzcash_title',
                'jazzcash_number',
                'payment_instructions',
            ]);
        });
    }
};
