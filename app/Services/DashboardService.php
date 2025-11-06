<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductLicenseKeys;
use App\Models\LicenseType;
use App\Models\Customer;
use App\Models\Order;
use App\Helpers\LicenseKeyHelper;
use Webpatser\Uuid\Uuid;
use Illuminate\Support\Facades\DB;
DB::enableQueryLog();

class DashboardService
{
    public static function getSummaryData()
    {
        $productCount = Product::where('status', 'ACTIVE')->count();
        $customerCount = Customer::where('status', 'ACTIVE')->count();
        $purchasedLicenseCount = ProductLicenseKeys::where('status', 'PURCHASED')->where('expiry_date', '>', date('Y-m-d H:i:s'))->count();
        $cmPurchasedLicenseCount = ProductLicenseKeys::where('status', 'PURCHASED')->where('purchased_date', 'LIKE', date('Y-m').'%')->count();
        $ordersCount = Order::count();
        $cmOrdersCount = Order::where('order_placed_at', 'LIKE', date('Y-m').'%')->count();

        $summary = [
            'product_count' => $productCount,
            'customer_count' => $customerCount,
            'purchased_license_count' => $purchasedLicenseCount,
            'cm_purchased_license_count' => $cmPurchasedLicenseCount,
            'orders_count' => $ordersCount,
            'cm_orders_count' => $cmOrdersCount,
        ];
        return json_encode(["status" => true, "code" => 200, "message" => 'Summary Retrieved Successfully', "data" => $summary, "status_code" => 200]);
    }

    public static function getTodayPurchasesData()
    {
        $todayPurchasedLicenses = ProductLicenseKeys::where('status', 'PURCHASED')->where('purchased_date', 'LIKE', date('Y-m-d').'%')->get();
        
        $customers = $todayPurchasedLicenses->map( function($license) {
            return $license->customer;
        });

        $products = $todayPurchasedLicenses->map( function($license) {
            return $license->product;
        });
        
        $licenseProducts = $todayPurchasedLicenses->map( function($license) {
            return $license->licenseProduct;
        });
        
        return json_encode(["status" => true, "code" => 200, "message" => 'Data Retrieved Successfully', "data" => $todayPurchasedLicenses, "status_code" => 200]);
    }

    public static function getProductBasedLicenseSummaryData()
    {
        $query = new ProductLicenseKeys();

        $query = $query->where([
            ['status', '=', 'PURCHASED'],
            ['purchased_date', 'LIKE', date('Y-m').'%']
        ])->selectRaw("id, COUNT(license_key) AS license_count, SUM(CASE WHEN license_type = 'TRIAL' THEN 1 ELSE 0 END) AS trial_count, SUM(CASE WHEN license_type = 'ANNUAL' THEN 1 ELSE 0 END) AS annual_count, SUM(CASE WHEN license_type = 'QUARTERLY' THEN 1 ELSE 0 END) AS quarterly_count, SUM(CASE WHEN license_type = 'LIFETIME' THEN 1 ELSE 0 END) AS life_time_count, product_id")->groupBy('product_id');

        $query = $query->whereHas('product');
        $licenseCountSummary = $query->orderBy('product_id')->get();

        $licenses = $licenseCountSummary->map( function($license) {
            if (!empty($license))
            {
                return $license->makeHidden(['id']);
            }
            else
            {
                return $license;
            }
        });

        $products = $licenseCountSummary->map( function($license) {
            if (!empty($license->product))
            {
                return $license->product->makeHidden(['id']);
            }
            else
            {
                return $license->product;
            }
        });

        //$licenseCountSummary['products'] = $products;
        //dd (DB::getQueryLog());

        return json_encode(["status" => true, "code" => 200, "message" => 'Data Retrieved Successfully', "data" => $licenseCountSummary, "status_code" => 200]);
    }

}
