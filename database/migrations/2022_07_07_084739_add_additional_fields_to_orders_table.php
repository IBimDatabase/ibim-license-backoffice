<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_uuid')->nullable()->after('id');
            $table->unsignedBigInteger('wp_order_id')->nullable()->after('order_uuid');
            $table->string('order_type')->nullable()->after('order_reference_no');
            $table->string('order_status')->nullable()->after('order_type');
            $table->string('payment_status')->nullable()->after('order_status');
            $table->decimal('tax', 10, 2)->nullable()->after('payment_status');
            $table->decimal('discount', 10, 2)->nullable()->after('tax');
            $table->decimal('total_price', 10, 2)->nullable()->after('discount');
            $table->unsignedBigInteger('customer_id')->nullable()->after('total_price');
            $table->renameColumn('order_info', 'additional_info');
            $table->longText('wp_order_json')->nullable()->after('order_info');
            $table->string('status')->nullable()->after('wp_order_json');
            $table->dateTime('paid_at')->nullable()->after('order_placed_at');
            $table->dateTime('cancelled_at')->nullable()->after('paid_at');
            $table->unsignedBigInteger('created_by')->nullable()->after('cancelled_at');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');

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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('order_uuid');
            $table->dropColumn('wp_order_id');
            $table->dropColumn('order_type');
            $table->dropColumn('order_status');
            $table->dropColumn('payment_status');
            $table->dropColumn('tax');
            $table->dropColumn('discount');
            $table->dropColumn('total_price');
            $table->dropColumn('additional_info');
            $table->dropColumn('wp_order_json');
            $table->dropColumn('status');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
            $table->dropColumn('paid_at');
            $table->dropColumn('cancelled_at');
        });
    }
}
