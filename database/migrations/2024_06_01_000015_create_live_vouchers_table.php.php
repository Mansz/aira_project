<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('live_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('discount_type', ['percentage', 'amount']);
            $table->decimal('discount_value', 10, 2);
            $table->foreignId('live_stream_id')->constrained('live_streams')->onDelete('restrict');
            $table->string('description')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Add index for performance
        Schema::table('live_vouchers', function (Blueprint $table) {
            $table->index('live_stream_id');
            $table->index(['start_time', 'end_time']);
            $table->index('active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('live_vouchers');
    }
};
