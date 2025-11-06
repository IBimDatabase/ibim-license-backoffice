<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWpOrderItemIdToProductLicenseKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_license_keys', function (Blueprint $table) {
            $table->unsignedBigInteger('wp_order_item_id')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_license_keys', function (Blueprint $table) {
            $table->dropColumn('wp_order_item_id');
        });
    }
}
