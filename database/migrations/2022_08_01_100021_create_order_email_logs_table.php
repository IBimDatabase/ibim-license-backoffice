<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderEmailLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email_uuid')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('entity_type')->nullable();
            $table->string('email_to')->nullable();
            $table->string('subject')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_email_logs');
    }
}
