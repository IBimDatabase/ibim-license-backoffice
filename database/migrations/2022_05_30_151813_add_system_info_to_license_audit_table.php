<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSystemInfoToLicenseAuditTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('license_audit', function (Blueprint $table) {
            $table->text('system_info')->nullable()->after('current_expiry_date');
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
            $table->dropColumn('system_info');
        });
    }
}
