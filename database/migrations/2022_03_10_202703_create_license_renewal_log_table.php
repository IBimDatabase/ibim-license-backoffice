<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicenseRenewalLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('license_renewal_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('license_id')->nullable();
            $table->string('license_renewal_uuid')->nullable();
            $table->string('license_key')->nullable();
            $table->string('previous_license_code')->nullable();
            $table->string('current_license_code')->nullable();
            $table->string('expiry_duration')->nullable();
            $table->dateTime('previous_expiry_date')->nullable();
            $table->dateTime('current_expiry_date')->nullable();
            $table->unsignedBigInteger('renewed_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('license_id')->references('id')->on('product_license_keys')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('renewed_by')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('license_renewal_log');
    }
}
