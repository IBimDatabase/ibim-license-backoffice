<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPackageIdToProductLicenseKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_license_keys', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable()->after('product_id');

            $table->foreign('package_id')->references('id')->on('packages')->onUpdate('CASCADE')->onDelete('CASCADE');
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
            $table->dropColumn('package_id');
        });
    }
}
