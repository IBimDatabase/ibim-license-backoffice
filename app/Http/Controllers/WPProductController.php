<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WPProductService;
use App\Validators\WPProductsValidator;

class WPProductController extends Controller
{
    public function getProducts(Request $request)
    {
        $data = $request->all();

        $response = WPProductService::getProductsData($data);
        
        if ($response['status'] === true)
        {
            return response()->json(["status" => $response['status'], "code" => 200, "message" => "Product Retrieved Successfully", "data" => $response['data']], 200); 
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
        if ($validation === true) 
        {
            $response = WPProductService::createProductData($data);
            
            if ($response['status'] === true)
            {
                return response()->json(["status" => $response['status'], "code" => 200, "message" => "Product Created Successfully", "data" => $response['data']], 200); 
            }
            else 
            {
                return response()->json(["status" => $response['status'], "code" => 422, "message" => "Woo-Commerce Exception Error", "data" => $response['data']], 422); 
            }
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }

    public function updateProduct(Request $request)
    {
        $data = $request->all();

        $validation = WPProductsValidator::updateProductValidator($data);
        if ($validation === true) 
        {
            $response = WPProductService::updateProductData($data);
            
            if ($response['status'] === true)
            {
                return response()->json(["status" => $response['status'], "code" => 200, "message" => "Product Updated Successfully", "data" => $response['data']], 200); 
            }
            else 
            {
                return response()->json(["status" => $response['status'], "code" => 422, "message" => "Woo-Commerce Exception Error", "data" => $response['data']], 422); 
            }
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }

    public function deleteProduct(Request $request)
    {
        $data = $request->all();

        $validation = WPProductsValidator::deleteProductValidator($data);
        if ($validation === true) 
        {
            $response = WPProductService::deleteProductData($data);

            if ($response['status'] === true)
            {
                return response()->json(["status" => $response['status'], "code" => 200, "message" => "Product Deleted Successfully", "data" => $response['data']], 200); 
            }
            else 
            {
                return response()->json(["status" => $response['status'], "code" => 422, "message" => "Woo-Commerce Exception Error", "data" => $response['data']], 422); 
            }
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }

    public function sendProductInfo(Request $request)
    {
        $data = $request->all();

        $validation = true; //WPProductsValidator::sendProductInfoValidator($data);
        if ($validation === true) 
        {
            $response = WPProductService::sendProductInfoData($data);

            if ($response['status'] === true)
            {
                return response()->json(["status" => $response['status'], "code" => 200, "message" => "Product Updated Successfully", "data" => $response['data']], 200); 
            }
            else 
            {
                return response()->json(["status" => $response['status'], "code" => 422, "message" => "Woo-Commerce Exception Error", "data" => $response['data']], 422); 
            }
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }
}
