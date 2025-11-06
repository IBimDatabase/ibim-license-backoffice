<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WPOrderService;
use App\Validators\WPOrdersValidator;

class WPOrderController extends Controller
{
    public function getOrders(Request $request)
    {
        $data = $request->all();

        $response = WPOrderService::getOrdersData($data);

        if ($response['status'] === true)
        {
            return response()->json(["status" => $response['status'], "code" => 200, "message" => "Orders Retrieved Successfully", "data" => $response['data']], 200); 
        }
        else 
        {
            return response()->json(["status" => $response['status'], "code" => 422, "message" => "Woo-Commerce Exception Error", "data" => $response['data']], 422); 
        }
    }

    public function createOrder(Request $request)
    {
        $data = $request->all();

        $validation = WPOrdersValidator::createOrderValidator($data);
        if ($validation === true) 
        {
            $response = WPOrderService::createOrderData($data);

            if ($response['status'] === true)
            {
                return response()->json(["status" => $response['status'], "code" => 200, "message" => "Order Created Successfully", "data" => $response['data']], 200); 
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

    public function updateOrder(Request $request)
    {
        $data = $request->all();

        $validation = WPOrdersValidator::updateOrderValidator($data);
        if ($validation === true) 
        {
            $response = WPOrderService::updateOrderData($data);

            if ($response['status'] === true)
            {
                return response()->json(["status" => $response['status'], "code" => 200, "message" => "Order Updated Successfully", "data" => $response['data']], 200); 
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

    public function deleteOrder(Request $request)
    {
        $data = $request->all();

        $validation = WPOrdersValidator::deleteOrderValidator($data);
        if ($validation === true) 
        {
            $response = WPOrderService::deleteOrderData($data);

            if ($response['status'] === true)
            {
                return response()->json(["status" => $response['status'], "code" => 200, "message" => "Order Deleted Successfully", "data" => $response['data']], 200); 
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

    public function sendOrderInfo(Request $request)
    {
        $data = $request->all();

        $validation = true; //WPOrdersValidator::sendOrderInfoValidator($data);
        if ($validation === true) 
        {
            $response = WPOrderService::sendOrderInfoData($data);
            
            if ($response['status'] === true)
            {
                return response()->json(["status" => $response['status'], "code" => 200, "message" => "Order has been successfully placed", "data" => $response['data']], 200); 
            }
            else 
            {
                if (@$response['message'])
                {
                    return response()->json(["status" => $response['status'], "code" => 422, "message" => @$response['message'], "data" => ""], 422); 
                }
                else
                {
                    return response()->json(["status" => $response['status'], "code" => 422, "message" => "Woo-Commerce Exception Error", "data" => $response['data']], 422); 
                }
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
