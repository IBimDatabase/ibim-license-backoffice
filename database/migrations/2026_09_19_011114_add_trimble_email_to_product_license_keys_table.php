<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('product_license_keys', function (Blueprint $table) {
        $table
            ->string('trimble_email', 150)
            ->nullable()
            ->index()
            ->after('mac_address');
    });
}

public function down()
{
    Schema::table('product_license_keys', function (Blueprint $table) {
        $table->dropIndex(
            'product_license_keys_trimble_email_index'
        );

        $table->dropColumn('trimble_email');
    });
}
};
