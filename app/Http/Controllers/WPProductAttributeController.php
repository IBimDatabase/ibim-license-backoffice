<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WPProductAttributeService;
use App\Validators\WPProductsValidator;

class WPProductAttributeController extends Controller
{
    public function getProductAttributeTerms(Request $request)
    {
        $data = $request->all();

        $response = WPProductAttributeService::getProductAttributeTermsData($data);

        if ($response['status'] === true)
        {
            return response()->json(["status" => $response['status'], "code" => 200, "message" => "Product Attribute Terms Retrieved Successfully", "data" => $response['data']], 200); 
        }
        else 
        {
            return response()->json(["status" => $response['status'], "code" => 422, "message" => "Woo-Commerce Exception Error", "data" => $response['data']], 422); 
        }
    }

    public function createProduct(Request $request)
    {
        $data = $request->all();

        $validation = WPProductsValidator::createProductValidator($data);

        if ($response['status'] === true)
        {
            return response()->json(["status" => $response['status'], "code" => 200, "message" => "Product Attribute Terms Created Successfully", "data" => $response['data']], 200); 
        }
        else 
        {
            return response()->json(["status" => $response['status'], "code" => 422, "message" => "Woo-Commerce Exception Error", "data" => $response['data']], 422); 
        }
    }

    public function updateProduct(Request $request)
    {
        $data = $request->all();

        $validation = WPProductsValidator::updateProductValidator($data);

        if ($response['status'] === true)
        {
            return response()->json(["status" => $response['status'], "code" => 200, "message" => "Product Attribute Terms Updated Successfully", "data" => $response['data']], 200); 
        }
        else 
        {
            return response()->json(["status" => $response['status'], "code" => 422, "message" => "Woo-Commerce Exception Error", "data" => $response['data']], 422); 
        }
    }

    public function deleteProduct(Request $request)
    {
        $data = $request->all();

        $validation = WPProductsValidator::deleteProductValidator($data);
        
        if ($response['status'] === true)
        {
            return response()->json(["status" => $response['status'], "code" => 200, "message" => "Product Attribute Terms Deleted Successfully", "data" => $response['data']], 200); 
        }
        else 
        {
            return response()->json(["status" => $response['status'], "code" => 422, "message" => "Woo-Commerce Exception Error", "data" => $response['data']], 422); 
        }
    }
}
