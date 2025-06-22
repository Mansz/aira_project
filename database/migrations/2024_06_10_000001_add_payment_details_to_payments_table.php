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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('amount');
            $table->string('bank_name')->nullable()->after('payment_method');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_holder')->nullable()->after('bank_account_number');
            $table->string('card_type')->nullable()->after('bank_account_holder');
            $table->string('card_last4')->nullable()->after('card_type');
            $table->timestamp('verified_at')->nullable()->after('card_last4');
            $table->foreignId('verified_by')->nullable()->constrained('admins')->onDelete('set null')->after('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'payment_method',
                'bank_name',
                'bank_account_number',
                'bank_account_holder',
                'card_type',
                'card_last4',
                'verified_at',
                'verified_by'
            ]);
        });
    }
};
