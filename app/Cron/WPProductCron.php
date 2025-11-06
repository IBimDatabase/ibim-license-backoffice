<?php
namespace App\Cron;

use App\Models\ProductLicenseKeys;
use App\Models\LicenseProduct;
use App\Models\Product;
use App\Services\WPProductService;
use App\Services\ProductsService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

Class WPProductCron
{
    public function __invoke()
    {
        $wpProducts = WPProductService::getProductsData(NULL, 20);        
        
        foreach($wpProducts as $wpProduct)
        {
            if (is_array($wpProduct))
            {
                foreach($wpProduct as $wpProductData)
                {
                    if (isset($wpProductData->id) && !empty($wpProductData->id))
                    {
                        $product = Product::where('wp_product_id', $wpProductData->id)->first();
                        if (!empty($product))
                        {
                            $modified_gmt = date('Y-m-d H:i:s', strtotime(@$wpProductData->modified_gmt));
                            $modified_ist = Carbon::createFromFormat('Y-m-d H:i:s', $modified_gmt, 'UTC');
                            $modified_ist->setTimezone('Asia/kolkata');

                            Log::debug('WP Product Sync Log - PRODUCT_CRON_UPDATE_MODULE: wp_product_id: '. @$wpProductData->id. ', wp_updated_time_ist:' .date('Y-m-d H:i:s', strtotime(@$modified_ist)). ', BO_updated_time_ist: ' . date('Y-m-d H:i:s', strtotime(@$product->updated_at)) . ', Recently_updated: ' . (date('Y-m-d H:i:s', strtotime(@$wpProductData->modified_gmt)) > date('Y-m-d H:i:s', strtotime(@$product->updated_at))));

                            if (date('Y-m-d H:i:s', strtotime($modified_ist)) > date('Y-m-d H:i:s', strtotime(@$product->updated_at)))
                            {
                                WPProductService::sendProductInfoData(['id' => $wpProductData->id]);
                            }
                        }
                        else
                        {
                            Log::debug('WP Product Sync Log - PRODUCT_CRON_CREATE_MODULE: wp_product_id: '. @$wpProductData->id);
                            WPProductService::sendProductInfoData(['id' => $wpProductData->id]);
                        }
                    }
                }
            }
        }
    }
}