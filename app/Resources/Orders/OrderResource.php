<?php

namespace App\Resources\Orders;

class OrderResource
{
    public static function order_details($order, $orderItem = NULL)
    {
        $response = [];
        $response['order_id'] = @$order->id;
        $response['order_uuid'] = @$order->order_uuid;
        $response['wp_order_id'] = @$order->wp_order_id;
        $response['order_source'] = @$order->order_source;
        $response['source'] = @$order->source;
        $response['order_reference_no'] = @$order->order_reference_no;
        $response['order_type'] = @$order->order_type;
        $response['order_status'] = @$order->order_status;
        $response['payment_status'] = @$order->payment_status;
        $response['status'] = @$order->status;
        $response['order_date'] = @$order->order_placed_at;
        $response['created_at'] = @$order->created_at;

        if (!empty($order->customer))
        {
            $response['customer']['first_name'] = @$order->customer->first_name;
            $response['customer']['last_name'] = @$order->customer->last_name;
            $response['customer']['phone'] = @$order->customer->phone;
            $response['customer']['email'] = @$order->customer->email;
            $response['customer']['status'] = @$order->customer->status;
            $response['customer']['created_at'] = @$order->customer->created_at;
        }
        else if (!empty($order->license->customer))
        {
            $response['customer']['first_name'] = @$order->license->customer->first_name;
            $response['customer']['last_name'] = @$order->license->customer->last_name;
            $response['customer']['phone'] = @$order->license->customer->phone;
            $response['customer']['email'] = @$order->license->customer->email;
            $response['customer']['status'] = @$order->license->customer->status;
            $response['customer']['created_at'] = @$order->license->customer->created_at;
        }

        if (!empty($orderItem->product))
        {
            $response['product']['product_name'] = @$orderItem->product->product_name;
            $response['product']['product_code'] = @$orderItem->product->product_code;
        }
        else if (!empty($order->license->product))
        {
            $response['product']['product_name'] = @$order->license->product->product_name;
            $response['product']['product_code'] = @$order->license->product->product_code;
        }

        if (!empty($orderItem->licenseTyp))
        {
            $response['license_type']['name'] = @$orderItem->licenseType->name;
            $response['license_type']['code'] = @$orderItem->licenseType->code;
        }
        else if (!empty($order->license->licenseProduct->licenseType))
        {
            $response['license_type']['name'] = @$order->license->licenseProduct->licenseType->name;
            $response['license_type']['code'] = @$order->license->licenseProduct->licenseType->code;
        }

        return $response;
    }

    public static function create_order($order_info,$customer_info)
    {
        $data = [];
        $data['order_info']['id'] = @$order_info['order_uuid'];
        $data['order_info']['order_id'] = @$order_info['wp_order_id'];
        $data['order_info']['order_source'] = @$order_info['order_source'];
        $data['order_info']['order_reference_no'] = @$order_info['order_reference_no'];
        $data['order_info']['order_type'] = @$order_info['order_type'];
        $data['order_info']['order_status'] = @$order_info['order_status'];
        // $data['order_info']['payment_status'] = @$order_info['payment_status'];
        // $data['order_info']['tax'] = @$order_info['tax'];
        // $data['order_info']['discount'] = @$order_info['discount'];
        $data['order_info']['total_price'] = @$order_info['total_price'];
        // $data['order_info']['customer_id'] = @$order_info['customer_id'];
        $data['order_info']['source'] = @$order_info['source'];
        $data['order_info']['status'] = @$order_info['status'];
        // $data['order_info']['additional_info'] = @$order_info['additional_info'];
        $data['order_info']['order_placed_at'] = @$order_info['order_placed_at'];
        // $data['order_info']['cancelled_at'] = @$order_info['cancelled_at'];
        // $data['order_info']['created_by'] = @$order_info['created_by'];

        $data['customer_info']['id'] = @$customer_info['customer_uuid'];
        // $data['customer_info']['customer_id'] = @$customer_info['wp_customer_id'];
        $data['customer_info']['name'] = @$customer_info['user_name'];
        // $data['customer_info']['first_name'] = @$customer_info['first_name'];
        // $data['customer_info']['last_name'] = @$customer_info['last_name'];
        $data['customer_info']['email'] = @$customer_info['email'];
        $data['customer_info']['phone'] = @$customer_info['phone'];
        $data['customer_info']['status'] = @$customer_info['status'];
        // $data['customer_info']['created_by'] = @$customer_info['created_by'];
        // $data['customer_info']['updated_by'] = @$customer_info['updated_by'];
        return $data;
    }
}
