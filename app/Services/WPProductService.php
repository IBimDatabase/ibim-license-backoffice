<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Automattic\WooCommerce\Client as WooClient;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use App\Models\Product;
use App\Services\ProductsService;
use App\Helpers\CurlHelper;
use App\Helpers\ProductCodeHelper;
use Illuminate\Support\Facades\Http;
DB::enableQueryLog();
use Log;

class WPProductService
{
    public static function getProductsData($data=NULL, $perPage=10, $page=1)
    {
        $woocommerce = new WooClient(
            config('services.wp_api.url'),
            config('services.wp_api.client_id'),
            config('services.wp_api.client_secret'),
            [
                'wp_api' => true,
                'version' => 'wp/v2',
                'query_string_auth' => true
            ]
        ); 
        
        try {
            $bodyContent = $woocommerce->get('product/'.@$data['id']."?per_page=$perPage&page=$page");
            return ["status" => true, "data" => $bodyContent];
        }
        catch (HttpClientException $e) {
            $error = ["error" => [$e->getMessage()]];
            return ["status" => false, "data" => $error];
        }
    }

    public static function createProductData($data)
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
            $bodyContent = $woocommerce->post('products', $data);
            return ["status" => true, "data" => $bodyContent];
        }
        catch (HttpClientException $e) {
            $error = ["error" => [$e->getMessage()]];
            return ["status" => false, "data" => $error];
        }
    }


    public static function updateProductData($data)
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
           // 'acf' => [
           //     'product_id' => @$data['product_id']                
           // ],
            "meta_data" => [
                "id" => 4199,
                "key" => "product_id",
                "value" => @$data['product_id']
            ]
        ];

        try {
            $bodyContent = $woocommerce->put('products/'.@$data['id'], $updateData);
            return ["status" => true, "data" => $bodyContent];
        }
        catch (HttpClientException $e) {
            $error = ["error" => [$e->getMessage()]];
            return ["status" => false, "data" => $error];
        }
    }


    public static function curlUpdateProductData($data)
    {
        $request = new \GuzzleHttp\Client([
            'base_uri' => config('services.wp_api.url')
        ]);

        $options = [
            'auth' => [config('services.wp_api.client_id'), ('services.wp_api.client_secret')],
            $header = [
                "Content-Type" => "application/json", 
                "Accept" => "application/json"
            ]
        ];
        
        $formData = [
            "title" => $data["product_id"]
        ];

        try {
            $response = $request->put('wp-json/wp/v2/product/1276', $options, ['json' => $formData]);
            $bodyContent = json_decode($response->getBody()->getContents(), true);

            return ["status" => true, "data" => $bodyContent];
        }
        catch (HttpClientException $e) {
            $error = ["error" => [$e->getMessage()]];
            return ["status" => false, "data" => $error];
        }
    
    }


    public static function deleteProductData($data)
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
            $bodyContent = $woocommerce->delete('products/'.$data['id'], $postData);
            return ["status" => true, "data" => $bodyContent];
        }
        catch (HttpClientException $e) {
            $error = ["error" => [$e->getMessage()]];
            return ["status" => false, "data" => $error];
        }
    }


    public static function sendProductInfoData($data)
    {
        Log::debug("WP Product Input Data: id = ".@$data['id'] .', parent_id = '. @$data['parent_id']);
        Log::debug("WP Product All Input Data:". json_encode($data));

        if (key_exists('id', $data))
        {
            $wp_product_id = (!empty(@$data['parent_id'])) ? @$data['parent_id'] : @$data['id'];
            
            $url = config('services.wp_api.url').'wp-json/wc/v2/products/'.$wp_product_id;
            $headers = [
                "Content-Type" => "application/json", 
                "Accept" => "application/json"
            ];

            $result = CurlHelper::call_api($url, 'PUT', [], $headers);
            
            if ($result['status'] === true)
            {
                $wpProductArray = $result['data'];
                $product_id = $product_code = $product_prefix = NULL;

                if (!empty($wpProductArray['meta_data']))
                {    
                    foreach ($wpProductArray['meta_data'] as $meta_key => $meta_value)
                    {
                        if (!empty($meta_value['key']) && $meta_value['key'] == 'product_id')
                        {
                            $product_id = @$meta_value['value'];
                        }

                        if (!empty($meta_value['key']) && $meta_value['key'] == 'product_code')
                        {
                            $product_code = @$meta_value['value'];
                        }

                        if (!empty($meta_value['key']) && $meta_value['key'] == 'product_prefix')
                        {
                            $product_prefix = @$meta_value['value'];
                        }
                    }
                }
                
                if (empty($product_code))
                {
                    if (!preg_match("/\(copy\)/i", @$wpProductArray['name']) )
                    {
                        $product_code = ProductCodeHelper::createCodeFromName(@$wpProductArray['name']);
                        Log::debug("WP Product Sync Log - PRODUCT_CODE_GENERATE_MODULE: wp_product_id = ".$wp_product_id. ", product_code = ". $product_code);
                    }
                }
                
                $productQuery = Product::where('wp_product_id', $wp_product_id);

                if (!empty($product_code))
                {
                    $productQuery = $productQuery->orWhere( function($q) use ($product_code) {
                        $q->where('product_code', $product_code);
                        $q->whereNull('wp_product_id');
                    });
                }
                $product = $productQuery->first();
                
                if (!empty($product))
                {
                    $updateData = [
                        "id" => @$product['product_uuid'],
                        "wp_product_id" => $wp_product_id,
                        "product_name" => @$wpProductArray['name'],
                        "product_prefix" => ($product_prefix) ? $product_prefix : 'IBIM',
                        "product_code" => $product_code,
                        "wp_product_json" => json_encode($wpProductArray),
                    ];

                    if (@$wpProductArray['status'] == 'publish')
                    {
                        if (!empty($product_code))
                        {
                            $updateData['status'] = 'ACTIVE';
                        }
                        else
                        {
                            $updateData['status'] = 'INACTIVE';
                        }
                    }
                    else
                    {
                        $updateData['status'] = 'INACTIVE';
                    }

                    Log::debug("WP Product Sync Log - PRODUCT_UPDATE_DATA_MODULE: wp_product_id = ".@$updateData['wp_product_id']. ", product_code = ". @$updateData['product_code']);
                    
                    $productData = ProductsService::updateProductData($updateData);
                }
                else
                {
                    $insertData = [
                        "wp_product_id" => $wp_product_id,
                        "product_name" => @$wpProductArray['name'],
                        "product_prefix" => ($product_prefix) ? $product_prefix : 'IBIM',
                        "product_code" => $product_code,
                        "wp_product_json" => json_encode($wpProductArray),
                    ];

                    if (@$wpProductArray['status'] == 'publish')
                    {
                        if (!empty($product_code))
                        {
                            $insertData['status'] = 'ACTIVE';
                        }
                        else
                        {
                            $insertData['status'] = 'INACTIVE';
                        }
                    }
                    else
                    {
                        $insertData['status'] = 'INACTIVE';
                    }
                    
                    $productData = ProductsService::addProductData($insertData);
                }
                
                if (isset($productData) && !empty($productData))
                {
                    $productData = json_decode($productData);

                    if (!empty($wpProductArray['meta_data']))
                    {     
                        foreach ($wpProductArray['meta_data'] as $meta_key => $meta_value)
                        {
                            if (!empty($meta_value['key']) && $meta_value['key'] == 'product_id')
                            {
                                if (@$productData->status && @$productData->data->product_id != @$meta_value['value'])
                                {
                                    $payload = [
                                        "meta_data" => [
                                            [
                                                "id" =>  $meta_value['id'],
                                                "key" => $meta_value['key'],
                                                "value" => @$productData->data->product_id
                                            ]
                                        ]
                                    ];
                                    
                                    $url = config('services.wp_api.url').'wp-json/wc/v2/products/'.@$productData->data->wp_product_id;
                                    $headers = [
                                        "Content-Type" => "application/json", 
                                        "Accept" => "application/json"
                                    ];
                        
                                    $result = CurlHelper::call_api($url, 'PUT', $payload, $headers);
                                    
                                    if ($result['status'] === true)
                                    {
                                        $productUpdateData = [
                                            "id" => @$productData->data->product_uuid,
                                            "wp_product_json" => json_encode($result['data']),
                                        ];
                                        $response = ProductsService::updateWpProductJson($productUpdateData);
                                        return ["status" => $response['status'], "data" => $response['data']];
                                    }
                                    else
                                    {
                                        // Return Woo Commerce error
                                        return ["status" => false, "data" => $result['data']];
                                    }
                                }
                                else
                                {
                                    return ["status" => $productData->status, "data" => $productData->data];
                                }
                            }                            
                        }
                    }
                    else
                    {
                        return ["status" => $productData->status, "data" => $productData->data];
                    }
                }
                else
                {
                    return ["status" => false, "data" => "Something Went Wrong"];
                }

            }
            else
            {
                return ["status" => false, "data" => $result['data']];
            }
        }
        
    }

}
