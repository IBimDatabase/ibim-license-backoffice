<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWpProductIdToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('wp_product_id')->nullable()->after('product_uuid');
            $table->text('s3_file_path')->nullable()->after('package_content');
            $table->longText('wp_product_json')->nullable()->after('s3_file_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('wp_product_id');
            $table->dropColumn('s3_file_path');
            $table->dropColumn('wp_product_json');
        });
    }
}
