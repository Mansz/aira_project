<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveAnalyticsTable extends Migration
{
    public function up()
    {
        Schema::create('live_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_stream_id')->constrained('live_streams')->onDelete('cascade');
            $table->integer('total_comments')->default(0);
            $table->integer('active_users')->default(0);
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('live_analytics');
    }
}
