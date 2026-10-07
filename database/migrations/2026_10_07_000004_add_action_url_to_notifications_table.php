<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('action_url')->nullable()->after('body');
            $table->string('icon')->nullable()->after('action_url'); // e.g. 'bi-bag-check', 'bi-truck', etc.
            $table->json('data')->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['action_url', 'icon', 'data']);
        });
    }
};
