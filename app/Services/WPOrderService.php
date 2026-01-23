<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Automattic\WooCommerce\Client as WooClient;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use Mail;
//use Webpatser\Uuid\Uuid;
//use GoldSpecDigital\LaravelUuid\Uuid; // Added_by_Abdul_Rehman_for_Upgrade Laravel
use App\Mail\GeneratedLicenseEmail;
use App\Services\LicenseKeyService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderAddress;
use App\Models\OrderDeduction;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderEmailLog;
use App\Jobs\GeneratedLicenseEmailJob;
DB::enableQueryLog();
use Log;

class WPOrderService
{
    public static function getOrdersData($data=NULL, $perPage=10, $page=1)
    {
        $woocommerce = new WooClient(
            config('services.wp_api.url'),
            config('services.wp_api.client_id'),
            config('services.wp_api.client_secret'),
            [
                'wp_api' => true,
                'version' => 'wc/v2',
                'query_string_auth' => true
            ]
        ); 

        try {
            $bodyContent = $woocommerce->get('orders/'.@$data['id']."?per_page=$perPage&page=$page");
            return ["status" => true, "data" => $bodyContent];
        }
        catch (HttpClientException $e) {
            $error = ["error" => [$e->getMessage()]];
            return ["status" => false, "data" => $error];
        }
    }

    public static function createOrderData($data)
    {
        $woocommerce = new WooClient(
            config('services.wp_api.url'),
            config('services.wp_api.client_id'),
            config('services.wp_api.client_secret'),
            [
                'wp_api' => true,
                'version' => 'wc/v2',
                'query_string_auth' => true
            ]
        );

        try {
            $bodyContent = $woocommerce->post('orders', $data);
            return ["status" => true, "data" => $bodyContent];
        }
        catch (HttpClientException $e) {
            $error = ["error" => [$e->getMessage()]];
            return ["status" => false, "data" => $error];
        }
    }


    public static function updateOrderData($data)
    {
        $woocommerce = new WooClient(
            config('services.wp_api.url'),
            config('services.wp_api.client_id'),
            config('services.wp_api.client_secret'),
            [
                'wp_api' => true,
                'version' => 'wc/v2',
                'query_string_auth' => true
            ]
        );

        $updateData = [

        ];

        try {
            $bodyContent = $woocommerce->put('orders/'.$data['id'], $updateData);
            return ["status" => true, "data" => $bodyContent];
        }
        catch (HttpClientException $e) {
            $error = ["error" => [$e->getMessage()]];
            return ["status" => false, "data" => $error];
        }
    }


    public static function deleteOrderData($data)
    {
        $woocommerce = new WooClient(
            config('services.wp_api.url'),
            config('services.wp_api.client_id'),
            config('services.wp_api.client_secret'),
            [
                'wp_api' => true,
                'version' => 'wc/v2',
                'query_string_auth' => true
            ]
        ); 

        $postData = [
            'force' => $data['force']
        ];

        try {
            $bodyContent = $woocommerce->delete('orders/'.$data['id'], $postData);
            return ["status" => true, "data" => $bodyContent];
        }
        catch (HttpClientException $e) {
            $error = ["error" => [$e->getMessage()]];
            return ["status" => false, "data" => $error];
        }
    }


    public static function sendOrderInfoData($data)
    {
        Log::debug("WP Order Input Data: id = ".@$data['id'] .', parent_id = '. @$data['parent_id']);

        $generatedLicensesArray = [];
        if (key_exists('id', $data))
        {
            $wpOrderResult = WPOrderService::getOrdersData($data);
            
            if ($wpOrderResult['status'] === true)
            {
                $wpOrderArray = json_decode(json_encode($wpOrderResult['data']), true);
                
                $wp_order_id = (!empty(@$wpOrderArray['parent_id'])) ? @$wpOrderArray['parent_id'] : @$wpOrderArray['id'];
                $order = Order::where('wp_order_id', $wp_order_id)->first();
                            
                if (!empty($order) && @$order->status != 'FAILED')
                {
                    Log::debug("WP order already placed: id = ".@$data['id'] .', parent_id = '. @$data['parent_id']);
                    return ["status" => false, "message" => "Order has been already placed", "data" => ""];      
                }
                else
                {
                    $customer = LicenseKeyService::saveCustomerDetails(@$wpOrderArray['billing']);
                    $orderInsertData = [
                        "wp_order_id" => @$wp_order_id,
                        "order_uuid" => Uuid::generate(4),
                        "order_type" => 'PURCHASE',
                        "order_status" => strtoupper(@$wpOrderArray['status']),
                        "tax" => @$wpOrderArray['total_tax'],
                        "discount" => @$wpOrderArray['discount_total'],
                        "total_price" => @$wpOrderArray['total'],
                        "customer_id" => (isset($customer)) ? $customer->id : NULL,
                        "source" => 'WOO_COMMERCE',
                        "status" => 'ACTIVE',
                        "order_reference_no" => @$wpOrderArray['order_key'],
                        "order_placed_at" => @$wpOrderArray['date_created'],
                        "paid_at" => @$wpOrderArray['date_paid'],
                        "wp_order_json" => json_encode($wpOrderArray),
                    ];
                    
                    $orderData = Order::insertRecord($orderInsertData);

                    if (isset($orderData->id))
                    {
                        $orderItemData = @$wpOrderArray['line_items'];

                        foreach ($orderItemData as $orderItem)
                        {
                            $wpLicenseCode = NULL;
                            foreach (@$orderItem['meta_data'] as $meta)
                            {
                                if (@$meta['key'] == 'pa_subscription')
                                {
                                    $wpLicenseCode = strtoupper(@$meta['value']);
                                    break;
                                }
                            }
                            
                            $productQuery = Product::where('wp_product_id', @$orderItem['product_id']);

                            if (!empty(@$orderItem['product_code']))
                            {
                                $productQuery = $productQuery->orWhere( function($q) use ($orderItem) {
                                    $q->where('product_code', @$orderItem['product_code']);
                                    $q->whereNull('wp_product_id');
                                });
                            }
                            $product = $productQuery->first();        

                            if (@$wpOrderArray['status'] != 'failed')
                            {
                                $licenseData = [
                                    "entity_type" => 'PRODUCT',
                                    "entity_ref_id" => @$product->id,
                                    "order_id" => @$orderData->id,
                                    "customer_id" => (isset($customer)) ? $customer->id : NULL,
                                    "license_code" => @$wpLicenseCode,
                                    "quantity" => @$orderItem['quantity'],
                                    "wp_order_item_id" => @$orderItem['id']
                                ];
    
                                $generatedLicensesData = LicenseKeyService::generateLicense($licenseData);
                                $generatedLicenses = json_decode($generatedLicensesData);
                                Log::debug('WP Order Sync Log - ORDER_CRON_CREATE_LICENSE_MODULE: Order_id:'. @$data['id'] .', licenseData: '. json_encode(@$licenseData));
                                if ($generatedLicenses->status === true)
                                {
                                    if (isset($generatedLicenses->data[0]))
                                    {
                                        $license_type_id = @$generatedLicenses->data[0]->license_product->type_id;
                                        $generatedLicensesArray[] = @$generatedLicenses->data[0];
                                    }
                                    else
                                    {
                                        $license_type_id = null;
                                    }
                                }
                                else
                                {
                                    return ["status" => $generatedLicenses->status, "data" => $generatedLicenses->data];      
                                }
                            }
                            
                            $orderItemInsertData = [
                                "order_id" => @$orderData->id,
                                "order_item_uuid" => Uuid::generate(4),
                                "entity_type" => 'PRODUCT',
                                "entity_ref_id" => @$product->id,
                                "license_type_id" => @$license_type_id,
                                "quantity" => @$orderItem['quantity'],
                                "unit_price" => @$orderItem['subtotal'],
                                "total_price" => @$orderItem['total'],
                                "additional_info" => json_encode(@$orderItem['meta_data']),
                                "status" => 'ACTIVE',
                            ];
                            
                            $orderItemData = OrderItem::insertRecord($orderItemInsertData);

                            if (isset($orderItemData->id))
                            {
                                $orderDeductionInsertData = [
                                    "deduction_uuid" => Uuid::generate(4),
                                    "order_id" => @$orderData->id,
                                    "order_item_id" => @$orderItemData->id,
                                    "deduction_type" => 'TAX',
                                    "deduction_ref_id" => NULL,
                                    "code" => NULL,
                                    "percentage" => NULL,
                                    "amount" => @$wpOrderArray['total_tax'],
                                    "additional_info" => NULL,
                                    "status" => 'ACTIVE',
                                ];
            
                                $orderDeductionData = OrderDeduction::insertRecord($orderDeductionInsertData);

                                $orderPaymentInsertData = [
                                    "payment_uuid" => Uuid::generate(4),
                                    "order_id" => @$orderData->id,
                                    "payment_ref_no" => '',
                                    "payment_mode" => strtoupper(@$wpOrderArray['payment_method']),
                                    "transaction_type" => strtoupper(str_replace(' (Stripe)', '', @$wpOrderArray['payment_method_title'])),
                                    "transaction_ref_no" => @$wpOrderArray['transaction_id'],
                                    "amount" => @$wpOrderArray['total'],
                                    "service_charges" => NULL,
                                    "additional_info" => NULL,
                                    "payment_url" => @$wpOrderArray['payment_url'],
                                    "transaction_status" => 'SUCCESS',
                                    "status" => 'ACTIVE',
                                ];
            
                                $orderPaymentData = OrderPayment::insertRecord($orderPaymentInsertData);
                            }
                        }
                    
                        $wpBillingData = @$wpOrderArray['billing'];
                        $orderBillingInsertData = [
                            "order_id" => $orderData->id,
                            "address_type" => 'HOME',
                            "first_name" => @$wpBillingData['first_name'],
                            "last_name" => @$wpBillingData['last_name'],
                            "company" => @$wpBillingData['company'],
                            "address_1" => @$wpBillingData['address_1'],
                            "address_2" => @$wpBillingData['address_2'],
                            "city" => @$wpBillingData['city'],
                            "state" => @$wpBillingData['state'],
                            "postcode" => @$wpBillingData['postcode'],
                            "country" => @$wpBillingData['country'],
                            "email" => @$wpBillingData['email'],
                            "phone" => @$wpBillingData['phone']
                        ];
    
                        $orderBillingData = OrderAddress::insertRecord($orderBillingInsertData);

                        if (!empty($orderBillingData))
                        {
                            if (@$wpOrderArray['status'] != 'failed' && count(@$generatedLicensesArray) > 0)
                            {
                                $mailData = [
                                    'order_id' => @$orderData->id,
                                    'generatedLicenses' => $generatedLicensesArray,
                                    'customer_fname' => @$wpBillingData['first_name'],
                                    'customer_lname' => @$wpBillingData['last_name'],
                                    'customer_email' => @$wpBillingData['email'],
                                    'subject' => 'IBIM - Order Placed',
                                ];
                                GeneratedLicenseEmailJob::dispatch($mailData); 
                                
                                $mailLogData = [
                                    'email_uuid' => Uuid::generate(4),
                                    'customer_id' => (isset($customer)) ? $customer->id : NULL,
                                    'entity_type' => 'PRODUCT',
                                    'email_to' => @$wpBillingData['email'],
                                    'subject' => 'IBIM - Order Placed',
                                ];
                                OrderEmailLog::insertRecord($mailLogData);
                                return ["status" => true, "data" => ['Generated Licenses' => $generatedLicensesArray]];
                            }
                            else
                            {
                                return ["status" => false, "message" => "Order failed", "data" => ""];
                            }     
                        }
                    }
                    else
                    {
                        return ["status" => false, "data" => ""];
                    }
                }
            }
            else
            {
                // Return Woo Commerce error
                return ["status" => false, "data" => $wpOrderResult['data']];
            }
        }        
    }

}
