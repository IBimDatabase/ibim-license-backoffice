<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductAvailableSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_available_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('license_type_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->longText('additional_info')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('license_type_id')->references('id')->on('license_products')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('product_id')->references('id')->on('products')->onUpdate('CASCADE')->onDelete('CASCADE');       
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_available_subscriptions');
    }
}
