<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('group_no')->unique();
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('delivery_total')->default(0);
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('grand_total');
            $table->enum('payment_method', ['cod', 'wallet', 'card'])->default('cod');
            
            // Address snapshot fields
            $table->string('shipping_full_name');
            $table->string('shipping_phone');
            $table->string('shipping_city');
            $table->string('shipping_area');
            $table->text('shipping_address_line');

            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_group_id')->constrained('order_groups')->cascadeOnDelete();
            $table->string('order_no')->unique();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('delivery_fee')->default(0);
            $table->unsignedInteger('commission')->default(0);
            $table->unsignedInteger('total');
            $table->string('status')->default('placed')->index();
            $table->string('payment_status')->default('unpaid')->index();
            $table->string('rider_name')->nullable();
            $table->string('rider_phone')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_groups');
    }
};
