<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHistoryDataToLicenseAuditTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('license_audit', function (Blueprint $table) {
            $table->string('license_audit_uuid')->nullable()->after('license_id');
            $table->string('entry_type')->nullable()->after('license_audit_uuid');
            $table->string('previous_license_code')->nullable()->after('mac_address');
            $table->string('current_license_code')->nullable()->after('previous_license_code');
            $table->string('expiry_duration')->nullable()->after('current_license_code');
            $table->dateTime('previous_expiry_date')->nullable()->after('expiry_duration');
            $table->dateTime('current_expiry_date')->nullable()->after('previous_expiry_date');
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
            $table->dropColumn('license_audit_uuid');
            $table->dropColumn('entry_type');
            $table->dropColumn('previous_license_code');
            $table->dropColumn('current_license_code');
            $table->dropColumn('expiry_duration');
            $table->dropColumn('previous_expiry_date');
            $table->dropColumn('current_expiry_date');
        });
    }
}
