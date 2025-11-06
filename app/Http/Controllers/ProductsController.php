<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\ProductsService;
use App\Validators\ProductsValidator;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductsController extends Controller
{
    public function productList()
    {
        return view('product-list');
    }

    public function getProducts(Request $request)
    {
        $data = $request->all();

        $response = ProductsService::getProductsData($data);
        $response = json_decode($response);

        return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
    }

    public function addProduct(Request $request)
    {
        $data = $request->all();

        $validation = ProductsValidator::addProductValidator($data);
        if ($validation === true) 
        {
            $response = ProductsService::addProductData($data);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
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

        $validation = ProductsValidator::updateProductValidator($data);
        if ($validation === true) 
        {
            $response = ProductsService::updateProductData($data);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422); 
        }
    }

    public function importProduct(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', -1);

        $data = $request->all();
        $validation = ProductsValidator::importProductValidator($data);
        if ($validation === true) 
        {
            $response = ProductsService::importProductData($request->file('importedFile'));
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else 
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }

    public function exportProduct(Request $request)
    {
        return Excel::download(new ProductsExport, 'Products.xlsx');
    }

    public function syncWooCommerceProduct(Request $request)
    {
        $data = $request->all();

        $validation = ProductsValidator::syncWooCommerceProductValidator($data);
        if ($validation === true) 
        {
            $response = ProductsService::syncWooCommerceProduct($data);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
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

        $existProduct = Product::where('product_uuid', $data['id'])->first();

        if (!empty(@$existProduct->id))
        {
            $updateData = ['updated_by' => auth()->user()->id];
            $products = Product::updateAndDeleteRecord($updateData, $data['id']);
            return response()->json(["status" => true, "code" => 200, "message" => 'Product Deleted Successfully', "data" => $products], 200);
        }
        else
        {
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => "", "status_code" => 422]);
        }
    }

}
