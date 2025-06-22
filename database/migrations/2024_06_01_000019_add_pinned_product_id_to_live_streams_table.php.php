<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPinnedProductIdToLiveStreamsTable extends Migration
{
    public function up()
    {
        Schema::table('live_streams', function (Blueprint $table) {
            $table->foreignId('pinned_product_id')->nullable()->constrained('products')->onDelete('set null')->after('stream_token');
        });
    }

    public function down()
    {
        Schema::table('live_streams', function (Blueprint $table) {
            $table->dropForeign(['pinned_product_id']);
            $table->dropColumn('pinned_product_id');
        });
    }
}
