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
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source')->index()->comment('Source of the webhook (e.g., whatsapp, payment-gateway)');
            $table->string('event_type')->index()->comment('Type of event received');
            $table->json('payload')->comment('Raw webhook payload');
            $table->json('headers')->nullable()->comment('Request headers');
            $table->string('ip_address')->nullable();
            $table->string('status')->default('pending')->comment('pending, success, failed');
            $table->json('response')->nullable()->comment('Response sent back');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Add indexes for common queries
            $table->index(['source', 'event_type']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
