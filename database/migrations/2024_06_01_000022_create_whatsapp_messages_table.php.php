<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->nullable(); // WhatsApp message ID
            $table->string('phone_number'); // Nomor telepon
            $table->text('message'); // Pesan
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending'); // Status pesan
            $table->enum('direction', ['inbound', 'outbound'])->default('outbound'); // Arah pesan
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // ID pengguna
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete(); // ID pesanan
            $table->json('metadata')->nullable(); // Data tambahan pesan WhatsApp
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};