<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrdersValidator
{

    const messages = [
        'order_id.required' => 'Order ID is required.',
        'type.required' => 'Order type is required.',
        'status.required' => 'Order status is required.',
        'order_amount.required' => 'Total price is required.',
        'id.required' => 'Customer ID is required.',
        'order_number.required' => 'Order reference number is required.',
        'name.required' => 'Username is required.',
        'email.required' => 'Email is required.',
        'email.email' => 'Invalid email format.',
        'phone.required' => 'Phone number is required.',
    ];

    public static function syncWooCommerceOrderValidator($data)
    {
        $rules = [
            'id' => 'required|alpha_dash|exists:App\Models\Order,order_uuid,deleted_at,NULL',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails())
        {
            return $validator;
        }
        else
        {
            return true;
        }
    }

    public static function create_order_data($request_data)
    {
        $validation_messages=self::messages;
        $validation_messages['order_info.order_id.required']="Order id is required";
        $validation_messages['order_info.type.required']="Order type is required";
        $validation_messages['order_info.order_number.required']="Order number is required";
        $validation_messages['order_info.order_amount.required']="Order amount is required";
        $validation_messages['order_info.date.required']="Order date is required";
        $validation_messages['order_info.date.date_format']="Invalid order date format(Y-m-d).";
        $validation_messages['customer_info.name.required']="Customer name is required.";
        $validation_messages['customer_info.email.required']="Customer email is required.";
        $validation_messages['customer_info.email.email']="Customer email is invalid.";
        $validation_messages['order_items.*.type.required']="Order item type is required.";
        $validation_messages['order_items.*.license_type.required']="Order item license type is required.";
        $validation_messages['order_items.*.product_id.required_if']="Order item product ID is required.";
        $validation_messages['order_items.*.package_id.required_if']="Order item package ID is required.";
        $validation_messages['order_items.*.quantity.required']="Order item quantity is required.";
        $validation_messages['order_items.required']="Order item is required.";
        $rules = [
            'order_info.order_id' => 'required',
            'order_info.type' => 'nullable',
            'order_info.order_amount' => 'required',
            'order_info.order_number' => 'required',
            'order_info.date' => 'required|date_format:Y-m-d',
            'customer_info.name' => 'required',
            'customer_info.email' => 'required|email',
            // 'customer_info.phone' => 'required',
            'order_items' => 'required|array',
            'order_items.*.type' => ['required', 'in:PRODUCT,PACKAGE'],
            'order_items.*.license_type' => ['required',Rule::exists('license_types', 'code')->whereNull('deleted_at')->where('status', 'AVAILABLE')],
            'order_items.*.product_id' => ['required_if:order_items.*.type,PRODUCT',Rule::exists('products', 'product_uuid')->where('status', 'ACTIVE')->whereNull('deleted_at')],
            'order_items.*.package_id' => ['required_if:order_items.*.type,PACKAGE',Rule::exists('packages', 'package_uuid')->where('status', 'AVAILABLE')->whereNull('deleted_at')],
            'order_items.*.quantity' => ['required', 'numeric','min:1','max:20']
        ];
        $validator = Validator::make($request_data, $rules, $validation_messages);

        return $validator;
    }
    public static function view_order_data($request_data)
    {
        $validation_messages=self::messages;
        $validation_messages['id.required']="Order id is required";
        $validation_messages['id.exists']="Order id is invalid";
        $rules = [
            'id' => ['required',Rule::exists('orders', 'order_uuid')->whereNull('deleted_at')],
        ];
        $validator = Validator::make($request_data, $rules, $validation_messages);

        return $validator;
    }
}
