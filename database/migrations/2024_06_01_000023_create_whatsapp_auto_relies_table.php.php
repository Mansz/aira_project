<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_auto_replies', function (Blueprint $table) {
            $table->id();
            $table->string('keyword');
            $table->text('response');
            $table->boolean('is_regex')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_auto_replies');
    }
};
