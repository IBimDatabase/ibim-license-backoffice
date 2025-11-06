<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Exports\OrdersExport;
use App\Validators\OrdersValidator;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\ResponseHelper;

class OrdersController extends Controller
{
    public function ordersList()
    {
        return view('order-list');
    }

    public function getOrders(Request $request)
    {
        $data = $request->all();

        $response = OrderService::getOrdersData($data);
        $response = json_decode($response);

        return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
    }

    public function syncWooCommerceOrder(Request $request)
    {
        $data = $request->all();

        $validation = OrdersValidator::syncWooCommerceOrderValidator($data);
        if ($validation === true)
        {
            $response = OrderService::syncWooCommerceOrder($data);
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

    public function exportOrder(Request $request)
    {
        return Excel::download(new OrdersExport, 'Orders.xlsx');
    }

    public function create_order_data(Request $request)
    {
        $users_info = [];
        $result=[];
        $request_data=$request->all();
        $errors = [];
        $validation_result = OrdersValidator::create_order_data($request_data);
        if ($validation_result->fails()) {
            $validation_response = ResponseHelper::RenderValidationResponse($validation_result, 'Order Data Creation process failed!');
            return response()->json($validation_response, 422);
        }
        if(empty($errors)){
            $validation_response = OrderService::create_order_data($request_data);
            if (!empty($validation_response['status'])) {
                $response = ResponseHelper::RenderSuccessResponse(@$validation_response['data'], 'Order placed successfully, License keys has been shared via email.');
            } else {
                $response = ResponseHelper::RenderErrorResponse(@$validation_response['data'], 'Order failed to placed.', 422);
            }
        }
        return response()->json($response, @$response['code']);
    }
    public function view_order_data(Request $request, $id)
    {
        $users_info = [];
        $result=[];
        $request_data=$request->all();
        $request_data['id'] = $id;
        $errors = [];
        $validation_result = OrdersValidator::view_order_data($request_data);
        if ($validation_result->fails()) {
            $validation_response = ResponseHelper::RenderValidationResponse($validation_result, 'View order failed!');
            return response()->json($validation_response, 422);
        }
        if(empty($errors)){
            $order_info = OrderService::view_order_data($request_data);
            if (!empty($order_info)) {
                $response = ResponseHelper::RenderSuccessResponse($order_info, 'Order view successfully.');
            } else {
                $response = ResponseHelper::RenderErrorResponse(@$validation_response['data'], 'Order failed to view.', 422);
            }
        }
        return response()->json($response, @$response['code']);
    }
}
