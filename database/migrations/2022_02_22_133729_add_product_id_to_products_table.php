<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductIdToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_prefix')->nullable()->after('product_name');
            $table->string('product_number')->nullable()->after('product_prefix');
            $table->string('product_id')->nullable()->after('product_number');
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
            $table->dropColumn('product_prefix');
            $table->dropColumn('product_number');
            $table->dropColumn('product_id');
        });
    }
}
