<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('customer_name');
            $table->string('customer_phone');
            
            // Address fields to match model expectations
            $table->string('address_street');
            $table->string('address_city');
            $table->string('address_province');
            $table->string('address_postal_code');
            
            // Courier fields to match model expectations
            $table->string('courier_name')->nullable();
            $table->string('courier_service')->nullable();
            $table->string('courier_tracking_number')->nullable();
            
            // Status: processing, in_transit, out_for_delivery, delivered
            $table->string('status')->default('processing');
            
            // Additional fields
            $table->decimal('weight', 8, 2)->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
