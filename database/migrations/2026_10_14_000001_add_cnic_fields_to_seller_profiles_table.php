<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->string('cnic_no', 30)->nullable()->after('description');
            $table->string('cnic_front', 255)->nullable()->after('cnic_no');
            $table->string('cnic_back', 255)->nullable()->after('cnic_front');
        });
    }

    public function down(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->dropColumn(['cnic_no', 'cnic_front', 'cnic_back']);
        });
    }
};
