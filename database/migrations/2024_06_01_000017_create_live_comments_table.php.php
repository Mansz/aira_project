<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('live_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_stream_id')->constrained('live_streams')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_order')->default(false);
            $table->string('order_code')->nullable();
            $table->integer('order_quantity')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('live_comments');
    }
};
