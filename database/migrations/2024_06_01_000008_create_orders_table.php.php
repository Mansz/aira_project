<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('total_price', 12, 2);
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->text('shipping_address');
            $table->string('payment_method');
            $table->string('status')->default('Menunggu Pembayaran');
            $table->string('tracking_number')->nullable();
            $table->string('shipping_courier')->nullable();
            $table->string('shipping_proof_path')->nullable();
            $table->string('fcm_token')->nullable();
            $table->timestamps();
        });

        
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
