<?php
namespace App\Cron;

use App\Models\Order;
use App\Services\WPOrderService;
use App\Services\OrdersService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

Class WPOrderCron
{
    public function __invoke()
    {
        $wpOrders = WPOrderService::getOrdersData(NULL, 20);        
        
        foreach($wpOrders as $wpOrder)
        {
            if (is_array($wpOrder))
            {
                foreach($wpOrder as $wpOrderData)
                {
                    if (isset($wpOrderData->id) && !empty($wpOrderData->id))
                    {
                        $order = Order::where('wp_order_id', $wpOrderData->id)->first();
                        if (empty($order))
                        {
                            Log::debug('WP Order Sync Log - ORDER_CRON_CREATE_MODULE: wp_order_id: '. @$wpOrderData->id);
                            WPOrderService::sendOrderInfoData(['id' => $wpOrderData->id]);
                        }
                    }
                }
            }
        }
    }
}