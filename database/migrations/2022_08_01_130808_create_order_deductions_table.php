<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDeductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_deductions', function (Blueprint $table) {
            $table->id();
            $table->string('deduction_uuid')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->string('deduction_type')->nullable();
            $table->string('deduction_ref_id')->nullable();
            $table->string('code')->nullable();            
            $table->decimal('percentage', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->longText('additional_info')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('order_id')->references('id')->on('orders')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('order_item_id')->references('id')->on('order_items')->onUpdate('CASCADE')->onDelete('CASCADE');       
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_deductions');
    }
}
