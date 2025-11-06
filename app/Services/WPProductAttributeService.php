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
DB::enableQueryLog();
use Log;

class WPProductAttributeService
{
    public static function getProductAttributesData($data)
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
            $bodyContent = $woocommerce->get('products/attributes/'.@$data['attribute_id']);
            return ["status" => true, "data" => $bodyContent];
        }
        catch (HttpClientException $e) {
            $error = ["error" => [$e->getMessage()]];
            return ["status" => false, "data" => $error];
        }
    }

    public static function getProductAttributeTermsData($data)
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
            $bodyContent = $woocommerce->get('products/attributes/'.@$data['attribute_id'].'/terms/'.@$data['term_id']);
            return ["status" => true, "data" => $bodyContent];
        }
        catch (HttpClientException $e) {
            $error = ["error" => [$e->getMessage()]];
            return ["status" => false, "data" => $error];
        }
    }

    public static function createProductAttributeTermsData($data)
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
            $bodyContent = $woocommerce->post('products/attributes/'.@$data['attribute_id'].'/terms', $data);
            return ["status" => true, "data" => $bodyContent];
        }
        catch (HttpClientException $e) {
            $error = ["error" => [$e->getMessage()]];
            return ["status" => false, "data" => $error];
        }
    }


    public static function updateProductAttributeTermsData($data)
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
            'name' => @$data['name'],
        ];

        try {
            $bodyContent = $woocommerce->put('products/attributes/'.@$data['attribute_id'].'/terms/'.@$data['term_id'], $updateData);
            return ["status" => true, "data" => $bodyContent];
        }
        catch (HttpClientException $e) {
            $error = ["error" => [$e->getMessage()]];
            return ["status" => false, "data" => $error];
        }
    }


    public static function deleteProductAttributeTermsData($data)
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
            $bodyContent = $woocommerce->delete('products/attributes/'.@$data['attribute_id'].'/terms/'.@$data['term_id'], $postData);
            return ["status" => true, "data" => $bodyContent];
        }
        catch (HttpClientException $e) {
            $error = ["error" => [$e->getMessage()]];
            return ["status" => false, "data" => $error];
        }
    }

}
