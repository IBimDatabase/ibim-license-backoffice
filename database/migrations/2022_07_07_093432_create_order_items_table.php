<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('order_item_uuid')->nullable();
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_ref_id')->nullable();
            $table->unsignedBigInteger('license_type_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->text('additional_info')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();  
            $table->softDeletes();

            $table->foreign('order_id')->references('id')->on('orders')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('license_type_id')->references('id')->on('license_types')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
    }
}
