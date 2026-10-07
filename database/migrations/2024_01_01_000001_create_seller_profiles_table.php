<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('farm_name');
            $table->string('location');
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_profiles');
    }
};
