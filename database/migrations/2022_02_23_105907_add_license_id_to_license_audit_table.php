<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLicenseIdToLicenseAuditTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('license_audit', function (Blueprint $table) {
            $table->unsignedBigInteger('license_id')->nullable()->after('id');

            $table->foreign('license_id')->references('id')->on('product_license_keys')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('license_audit', function (Blueprint $table) {
            $table->dropColumn('license_id');
        });
    }
}
