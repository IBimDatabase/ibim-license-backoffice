<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWpAttributeTermIdToLicenseTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('license_types', function (Blueprint $table) {
            $table->unsignedBigInteger('wp_attribute_term_id')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('license_types', function (Blueprint $table) {
            $table->dropColumn('wp_attribute_term_id');
        });
    }
}
