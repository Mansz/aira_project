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
        Schema::table('orders', function (Blueprint $table) {
            // Add shipping status field separate from order status
            $table->string('shipping_status')->default('processing')->after('status');
            
            // Add courier service details
            $table->string('courier_name')->nullable()->after('shipping_status');
            $table->string('courier_service')->nullable()->after('courier_name');
            
            // Rename existing tracking_number to ensure it exists
            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('courier_service');
            }
            
            // Add shipping cost
            $table->decimal('shipping_cost', 10, 2)->default(0)->after('tracking_number');
            
            // Add estimated delivery date
            $table->date('estimated_delivery_date')->nullable()->after('shipping_cost');
            
            // Add actual delivery date
            $table->date('delivered_at')->nullable()->after('estimated_delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_status',
                'courier_name',
                'courier_service',
                'shipping_cost',
                'estimated_delivery_date',
                'delivered_at'
            ]);
            
            // Don't drop tracking_number as it might have existed before
        });
    }
};
