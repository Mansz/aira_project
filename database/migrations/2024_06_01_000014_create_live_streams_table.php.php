<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveStreamsTable extends Migration
{
    public function up()
    {
        Schema::create('live_streams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, live, ended
            $table->string('room_id')->unique();
            $table->string('stream_key')->unique();
            $table->string('thumbnail_path')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('viewer_count')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // Pivot table for products featured in live streams
        Schema::create('live_stream_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_stream_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamp('featured_at')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('live_stream_products');
        Schema::dropIfExists('live_streams');
    }
}
