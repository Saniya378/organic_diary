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
            $table->unsignedInteger('shelf_life_days')->nullable()->after('produced_on')->comment('Shelf life in days from production date');
            $table->date('expires_at')->nullable()->index()->after('shelf_life_days')->comment('Batch expiry date');
            $table->timestamp('expiry_notified_at')->nullable()->after('expires_at')->comment('Timestamp when seller was alerted of approaching expiry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['shelf_life_days', 'expires_at', 'expiry_notified_at']);
        });
    }
};
