<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Order;

class OrdersSourceMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {       
        $bcOrders = Order::whereNull('wp_order_id')->get();

        if (count($bcOrders) > 0)
        {
            foreach ($bcOrders as $bcOrder) 
            {
                $updateData = [
                    'source' => 'BACK_OFFICE',
                    'order_status' => 'PROCESSING',
                    'status' => 'ACTIVE',
                ];
                Order::updateRecord($updateData, $bcOrder->id);
            }
        } 

        $wcOrders = Order::whereNotNull('wp_order_id')->get();

        if (count($wcOrders) > 0)
        {
            foreach ($wcOrders as $wcOrder) 
            {
                $updateData = [
                    'source' => 'WOO_COMMERCE',
                ];
                Order::updateRecord($updateData, $wcOrder->id);
            }
        }     
    }
}
