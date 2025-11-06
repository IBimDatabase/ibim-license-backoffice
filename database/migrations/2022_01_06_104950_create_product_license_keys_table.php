<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductLicenseKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_license_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('license_type_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('renewal_license_id')->nullable();
            $table->string('license_uuid')->nullable();
            $table->string('license_type')->nullable();
            $table->string('license_key')->nullable();
            $table->string('mac_address')->nullable();
            $table->text('license_info')->nullable();
            $table->dateTime('expiry_date')->nullable();
            $table->timestamp('purchased_date')->nullable();
            $table->string('status', 50)->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();  
            $table->softDeletes();

            $table->foreign('license_type_id')->references('id')->on('license_products')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('product_id')->references('id')->on('products')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('renewal_license_id')->references('id')->on('product_license_keys')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('customer_id')->references('id')->on('customers')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('order_id')->references('id')->on('orders')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_license_keys');
    }
}
