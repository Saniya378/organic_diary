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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('deal_no')->unique();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->string('title');
            $table->string('duration_type')->default('1_week'); // 3_days, 1_week, 1_month, custom
            $table->unsignedInteger('total_days')->default(7);
            $table->unsignedInteger('delivered_days')->default(0);
            $table->unsignedInteger('quantity_per_day')->default(1);
            $table->unsignedInteger('unit_price'); // single day price in PKR
            $table->unsignedInteger('discount_percent')->default(0); // e.g. 5, 10, 15
            $table->unsignedInteger('total_amount'); // final total price
            $table->string('delivery_slot')->default('morning'); // morning, evening
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('active'); // active, paused, completed, cancelled
            $table->string('payment_method')->default('cod'); // cod, card
            $table->string('payment_status')->default('pending'); // pending, paid
            $table->text('special_instructions')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('deal_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained('deals')->cascadeOnDelete();
            $table->unsignedInteger('day_number');
            $table->date('scheduled_date');
            $table->string('status')->default('scheduled'); // scheduled, dispatched, delivered, skipped
            $table->timestamp('delivered_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_deliveries');
        Schema::dropIfExists('deals');
    }
};
