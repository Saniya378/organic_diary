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
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->json('serviceable_cities')->nullable()->after('location');
            $table->text('serviceable_areas')->nullable()->after('serviceable_cities');
            $table->unsignedInteger('delivery_fee')->nullable()->after('serviceable_areas');
            $table->unsignedInteger('free_delivery_threshold')->nullable()->after('delivery_fee');
            $table->unsignedInteger('min_order_amount')->nullable()->after('free_delivery_threshold');
        });

        Schema::table('order_groups', function (Blueprint $table) {
            $table->string('delivery_slot')->default('morning')->after('payment_method');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_slot')->default('morning')->after('rider_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'serviceable_cities',
                'serviceable_areas',
                'delivery_fee',
                'free_delivery_threshold',
                'min_order_amount',
            ]);
        });

        Schema::table('order_groups', function (Blueprint $table) {
            $table->dropColumn('delivery_slot');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_slot');
        });
    }
};
