<?php

namespace App\Services;

use App\Models\Product;
use App\Helpers\LicenseKeyHelper;
//use Webpatser\Uuid\Uuid;
//use GoldSpecDigital\LaravelUuid\Uuid; 
use Illuminate\Support\Str;// Added_by_Abdul_Rehman_for_Upgrade Laravel
use Illuminate\Support\Facades\DB;
use App\Imports\ProductsImport;
use App\Services\WPProductService;
use Excel;
DB::enableQueryLog();
use Log;

class ProductsService
{
    public static function getProductsData($data, $perPage)
    {
        $query = new Product;

        if (!empty(@$data['product_name']))
            $query = $query->where('product_name', 'like', '%'. $data['product_name'] .'%');

        if (!empty(@$data['product_code']))
            $query = $query->where('product_code', 'like', '%'. $data['product_code'] .'%');

        if (!empty(@$data['product_id']))
            $query = $query->where('product_id', 'like', '%'. $data['product_id'] .'%');
        
        if (!empty(@$data['status']))
            $query = $query->where('status', 'like', $data['status']);

        if (!empty(@$data['sort_by']))
        {
            $sort_order = @$data['sort_order'] ? @$data['sort_order'] : 'ASC';
            $query = $query->orderBy(@$data['sort_by'], $sort_order);
        }
        else
        {
            $query = $query->orderBy((@$data['sort_by']) ? @$data['sort_by'] : 'created_at', (@$data['sort_order']) ? @$data['sort_order'] : 'DESC');
        }
       
        if (!key_exists('page', $data) || $data['page'] == 'all')
            $products = $query->paginate($perPage);
        else
            $products = $query->paginate($perPage);

        $products->map( function($product) {
            return $product->makeHidden(['id']);
        });

        //dd (DB::getQueryLog());
        return json_encode(["status" => true, "code" => 200, "message" => 'Products Retrieved Successfully', "data" => $products, "status_code" => 200]);
    }


    public static function addProductData($data)
    {
        $checkNameDuplication = Product::where('product_name', $data['product_name'])->first();

        if (!empty($checkNameDuplication))
        {
            $error = ["error" => ["The product name has already been taken."]];
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error, "status_code" => 422]);
        }
        
        $checkCodeDuplication = Product::where('product_code', $data['product_code'])->where('product_code', '!=', '')->whereNotNull('product_code')->first();

        if (!empty($checkCodeDuplication))
        {
            $error = ["error" => ["The product code has already been taken."]];
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error, "status_code" => 422]);
        }

        $lastProduct = Product::where('product_prefix', $data['product_prefix'])->orderBy('id', 'DESC')->first();

        if (!empty($lastProduct))
        {
            $productNumber = $lastProduct->product_number + 1;
        }
        else
        {
            $productNumber = 1001;
        }

        $description = json_encode([["Type" => "Text", "Content" => [key_exists('description', $data) ? $data['description'] : '']]]);
        $productData = [
            'product_uuid' => (string) Str::uuid(),
            'product_name' => @$data['product_name'],
            'product_prefix' => @$data['product_prefix'],
            'product_number' => $productNumber,
            'product_id' =>  @$data['product_prefix']. '-' .$productNumber,
            'description' => $description,
            'wp_product_id' => @$data['wp_product_id'],
            'wp_product_json' => @$data['wp_product_json'],
            'status' => @$data['status']
        ];

        if(!empty($data['product_code']))
        {
            $productData['product_code'] = @$data['product_code'];
        }

        if (auth()->user() !== null)
        {
            $productData['created_by'] = auth()->user()->id;
        }

        $product = Product::insertRecord($productData);
        $product = Product::find($product->id);
        $product->makeHidden(['id']);
        
        if ($product)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "Product Added Successfully", "data" => $product, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500]);
        }
    }


    public static function updateProductData($data)
    {
        $checkNameDuplication = Product::where('product_uuid', '!=', $data['id'])->where(function($q) use ($data) {
            $q->where('product_name', $data['product_name']);
        })->first();

        if (!empty($checkNameDuplication))
        {
            $error = ["error" => ["The product name has already been taken."]];
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error, "status_code" => 422]);
        }

        $existProduct = Product::where('product_uuid', $data['id'])->first();

        if ($existProduct->product_prefix != $data['product_prefix'])
        {
            $lastProduct = Product::where('product_prefix', $data['product_prefix'])->orderBy('id', 'DESC')->first();

            if (!empty($lastProduct))
            {
                $productNumber = $lastProduct->product_number + 1;
            }
            else
            {
                $productNumber = 1001;
            }
        }
        else
        {
            $productNumber = $existProduct->product_number;
        }
       
        $description = json_encode([["Type" => "Text", "Content" => [key_exists('description', $data) ? $data['description'] : '']]]);
        $productData = [
            'product_name' => $data['product_name'],
            'description' => $description,
            'wp_product_id' => @$data['wp_product_id'],
            'product_prefix' => $data['product_prefix'],
            'product_number' => $productNumber,
            'product_id' =>  $data['product_prefix']. '-' .$productNumber,
            'wp_product_json' => @$data['wp_product_json'],
            'status' => $data['status'],
        ];

        if(!empty($data['product_code']))
        {
            $productData['product_code'] = @$data['product_code'];
        }

        if (auth()->user() !== null)
        {
            $productData['updated_by'] = auth()->user()->id;
        }
        Log::debug("WP Product Sync Log - PRODUCT_UPDATE_MODULE: wp_product_id = ".@$data['wp_product_id']. ", product_code = ". @$data['product_code']);
        $product = Product::updateRecord($productData, $data['id']);
        $product->makeHidden(['id']);
        
        if ($product)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "Product Updated Successfully", "data" => $product, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500]);
        }
    }


    public static function updateWpProductJson($data)
    {
        $productData = [
            'wp_product_json' => @$data['wp_product_json']
        ];

        if (auth()->user() !== null)
        {
            $productData['updated_by'] = auth()->user()->id;
        }

        $product = Product::updateRecord($productData, $data['id']);
        $product->makeHidden(['id']);
        
        if ($product)
        {
            return ["status" => true, "code" => 200, "message" => "Product Updated Successfully", "data" => $product, "status_code" => 200];
        }
        else
        {
            return ["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500];
        }
    }

    
    public static function importProductData($ImportFile)
    {
        $import = new ProductsImport();
        $import->import($ImportFile);
        
        $errors = [];
        foreach ($import->failures() as $failure) {
            $errors[] = $failure->errors(); // Actual error messages from Laravel validator
        }

        if (count($errors) == 0)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "Product Added Successfully", "data" => "", "status_code" => 200]);
        }
        else if (count($errors) > 0)
        {
            $errors = array_map("unserialize", array_unique(array_map("serialize", $errors)));
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => ["error" => $errors], "status_code" => 422]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500]);
        }
    }

    public static function syncWooCommerceProduct($data)
    {
        $product = Product::where('product_uuid', @$data['id'])->first();
        $productSync = WPProductService::sendProductInfoData(['id' => @$product->wp_product_id]);
            
        if (!empty($productSync['status']) && $productSync['status'] == true)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "Product Updated Successfully", "data" => @$productSync['data'], "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 422, "message" => "There Is No Recent Updates", "data" => "", "status_code" => 422]);
        }
    }

}
