<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LicenseValidator
{

    public static function cancel_and_refund_subscription($data)
    {
        $validator = Validator::make($data, [
            'order_id' => ['required', Rule::exists('orders', 'order_uuid')->where('order_status','COMPLETED')->where('order_type', 'WEBSITE')->whereNull('deleted_at')],
            'entity_item_id'=>['required'],
        ]);

        if ($validator->fails())
        {
            return $validator;
        }
        else
        {
            return true;
        }
    }
    public static function renew_existing_orders($data)
    {
        $validator = Validator::make($data, [
            'order_id' => ['required', Rule::exists('orders', 'order_uuid')->where('order_status','COMPLETED')->where('order_type', 'WEBSITE')->whereNull('deleted_at')],
            'entity_item_id'=>['required'],
        ]);

        if ($validator->fails())
        {
            return $validator;
        }
        else
        {
            return true;
        }
    }
    public static function generateLicenseKeyValidator($data)
    {
        $rules = [
            'license_code' => 'required|max:30',
            'product_code' => 'required|max:150',
            'product_code.type' => 'required|alpha_dash|max:150',
            'product_code.value' => 'required|alpha_dash|max:150',
            'counts' => 'nullable|numeric|min:1|max:20',
            'order_source' => 'nullable|max:150',
            'order_reference_no' => 'nullable|alpha_num|max:150',
            'order_info' => 'nullable|max:255',
            'order_time' => 'nullable|date',
            'email' => ['nullable', 'max:50', 'regex:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/'],
            'phone_no' => ['nullable', 'regex:/^\+?[0-9]+$/'],
            'first_name' => ['nullable', 'max:150', 'regex:/^[a-zA-Z\s\.]+$/i'],
            'last_name' => ['nullable', 'max:150', 'regex:/^[a-zA-Z\s\.]+$/i'],
        ];
        $customErrorMsg = [
            'first_name.regex' => 'The first name must contain alphabets only.',
            'last_name.regex' => 'The last name must contain alphabets only.',
            'product_code.type.alpha_dash' => 'The product code type must not contain special characters.',
            'product_code.value.alpha_dash' => 'The product code value must not contain special characters.',
        ];

        $validator = Validator::make($data, $rules, $customErrorMsg);

        if ($validator->fails())
        {
            return $validator;
        }
        else
        {
            return true;
        }
    }


    public static function validateLicenseKey($data)
    {
        $validator = Validator::make($data, [
            'license_key' => ['required', 'max:20', 'regex:/[A-Z0-9]{4}\-[A-Z0-9]{4}\-[A-Z0-9]{4}\-[A-Z0-9]{4}/'],
        ]);

        if ($validator->fails())
        {
            return $validator;
        }
        else
        {
            return true;
        }
    }
    public static function licenseActivationValidation($data)
    {
        $messages=[
            'type.required'=>'Type is required.',
            'type.in'=>'Type is invalid, allowed vaalues are PRODUCT or PACKAGE.',
            'license_key.required'=>'License key is required.',
            'license_key.exists'=>'License key is invalid.',
            'product_code.required_if'=>'Product code is required, when type id Product.',
            'product_code.exists'=>'Product code is invalid.',
            'package_code.required_if'=>'Package code is required, when type id Package.',
            'package_code.exists'=>'Package code is invalid.',
        ];
        $data['type']=strtoupper(@$data['type']);
        $validator = Validator::make($data, [
            'type' => ['required', 'in:PRODUCT,PACKAGE'],
            'license_key' => ['required', 'max:20', 'regex:/[A-Z0-9]{4}\-[A-Z0-9]{4}\-[A-Z0-9]{4}\-[A-Z0-9]{4}/'],
            'product_code' => ['required_if:type,=,PRODUCT', Rule::exists('products', 'product_code')->whereNull('deleted_at')],
            'package_code' => ['required_if:type,=,PACKAGE', Rule::exists('packages', 'package_code')->whereNull('deleted_at')],
        ], $messages);

        if ($validator->fails())
        {
            return $validator;
        }
        else
        {
            return true;
        }
    }


    public static function validateLicenseKeyAndMac($data)
    {
        $validator = Validator::make($data, [
            'license_key' => ['required', 'max:20', 'regex:/[A-Z0-9]{4}\-[A-Z0-9]{4}\-[A-Z0-9]{4}\-[A-Z0-9]{4}/'],
            // 'mac_address' => ['required', 'max:17', 'regex:/[a-zA-Z0-9]{2}\-[a-zA-Z0-9]{2}\-[a-zA-Z0-9]{2}\-[a-zA-Z0-9]{2}\-[a-zA-Z0-9]{2}\-[a-zA-Z0-9]{2}|[a-zA-Z0-9]{2}\:[a-zA-Z0-9]{2}\:[a-zA-Z0-9]{2}\:[a-zA-Z0-9]{2}\:[a-zA-Z0-9]{2}\:[a-zA-Z0-9]{2}/i'],
            'mac_address' => ['required', 'max:200'],
            'product_code' => 'required|max:150',
        ]);

        if ($validator->fails())
        {
            return $validator;
        }
        else
        {
            return true;
        }
    }


    public static function licenseKeyDetailsValidator($data)
    {
        $rules = [
            // 'mac_address' => ['required', 'max:17', 'regex:/[a-zA-Z0-9]{2}\-[a-zA-Z0-9]{2}\-[a-zA-Z0-9]{2}\-[a-zA-Z0-9]{2}\-[a-zA-Z0-9]{2}\-[a-zA-Z0-9]{2}|[a-zA-Z0-9]{2}\:[a-zA-Z0-9]{2}\:[a-zA-Z0-9]{2}\:[a-zA-Z0-9]{2}\:[a-zA-Z0-9]{2}\:[a-zA-Z0-9]{2}/i'],
            'mac_address' => ['required', 'max:200'],
            'license_key' => 'required',
            'product_code' => 'required|max:150',
            'email' => ['nullable', 'max:50', 'regex:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/'],
            'phone_no' => ['nullable', 'regex:/^\+?[0-9]+$/'],
            'first_name' => 'nullable|max:150|string',
            'last_name' => 'nullable|max:150|string',
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


    public static function idPasswordValidator($data)
    {
        $rules = [
            'id' => 'required|alpha_dash|exists:App\Models\ProductLicenseKeys,license_uuid,deleted_at,NULL',
            'password' => 'required|string|password',
        ];

        $customErrorMsg = [
            'password.password' => 'The password doesn\'t match.'
        ];

        $validator = Validator::make($data, $rules, $customErrorMsg);

        if ($validator->fails())
        {
            return $validator;
        }
        else
        {
            return true;
        }
    }


    public static function idValidator($data)
    {
        $validator = Validator::make($data, [
            'id' => 'required|alpha_dash|exists:product_license_keys,license_uuid,deleted_at,NULL'
        ]);

        if ($validator->fails())
        {
            return $validator;
        }
        else
        {
            return true;
        }
    }


    public static function licenseRenewalValidator($data)
    {
        $rules = [
            'id' => 'required|alpha_dash|exists:App\Models\ProductLicenseKeys,license_uuid,deleted_at,NULL',
            'license_code' => 'required|exists:license_types,code,deleted_at,NULL,status,AVAILABLE|max:50',
            'renewal_type' => 'required|alpha_dash|in:"PRODUCT_CODE", "PACKAGE"|max:30',
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


    public static function licenseDeleteValidator($data)
    {
        $rules = [
            'id' => 'required|alpha_dash|exists:App\Models\ProductLicenseKeys,license_uuid,deleted_at,NULL',
            'delete_type' => 'required|alpha_dash|in:"PRODUCT_CODE", "PACKAGE"|max:30',
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

    public static function resetLicenseValidator($data)
    {
        $rules = [
            'id' => 'required|alpha_dash|exists:App\Models\ProductLicenseKeys,license_uuid,deleted_at,NULL',
            'type' => 'required|alpha_dash|in:PRODUCT,PACKAGE|max:30',
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

    public static function licenseActivateValidator($data)
    {
        $rules = [
            'id' => 'required|alpha_dash|exists:App\Models\ProductLicenseKeys,license_uuid,deleted_at,NULL',
            'activate_type' => 'required|alpha_dash|in:"PRODUCT_CODE", "PACKAGE"|max:30',
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

    public static function licenseDeactivateValidator($data)
    {
        $rules = [
            'id' => 'required|alpha_dash|exists:App\Models\ProductLicenseKeys,license_uuid,deleted_at,NULL',
            'deactivate_type' => 'required|alpha_dash|in:"PRODUCT_CODE", "PACKAGE"|max:30',
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

    public static function renew_existing_license_key($data)
    {
        $rules = [
            'order_item_id' => 'required|array',
            'order_item_id.*' => ['required','alpha_dash', Rule::exists('order_items', 'order_item_uuid')->whereNull('deleted_at')]
        ];

        $customErrorMsg = [
            'order_item_id.required' => 'Order item ID is required',
            'order_item_id.*.exists' => 'Order item ID is Invalid'
        ];

        $validator = Validator::make($data, $rules, $customErrorMsg);

        if ($validator->fails())
        {
            return $validator;
        }
        else
        {
            return true;
        }
    }
}
