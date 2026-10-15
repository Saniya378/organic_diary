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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('commission_rate', 5, 2)->nullable()->after('rejection_reason')->comment('Product specific commission percentage. Null falls back to default.');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->decimal('commission_rate', 5, 2)->nullable()->after('is_active')->comment('Category default commission percentage');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('commission_rate', 5, 2)->nullable()->after('line_total');
            $table->unsignedInteger('commission_amount')->default(0)->after('commission_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['commission_rate', 'commission_amount']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('commission_rate');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('commission_rate');
        });
    }
};
