<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('payment_uuid')->nullable();
            $table->string('payment_ref_no')->nullable();
            $table->string('payment_mode')->nullable();
            $table->string('transaction_type')->nullable();  
            $table->string('transaction_ref_no')->nullable();     
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('service_charges', 10, 2)->nullable();
            $table->longText('additional_info')->nullable();
            $table->text('payment_url')->nullable();
            $table->string('transaction_status')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
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
        Schema::dropIfExists('order_payments');
    }
}
