<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('live_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('live_stream_id')->constrained('live_streams')->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_amount', 12, 2);
            $table->foreignId('voucher_id')->nullable()->constrained('live_vouchers')->nullOnDelete();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->json('order_details');
            $table->timestamps();

            // Indexes for faster lookups and reporting
            $table->index(['live_stream_id', 'created_at']);
            $table->index(['buyer_id', 'created_at']);
            $table->index('voucher_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('live_orders');
    }
};