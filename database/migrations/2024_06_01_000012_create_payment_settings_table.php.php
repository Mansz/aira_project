<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('payment_type');
            $table->string('name');
            $table->string('account_number');
            $table->string('account_name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('logo_path')->nullable();
            $table->json('instructions')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_settings');
    }
}
