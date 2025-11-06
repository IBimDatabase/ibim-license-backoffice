<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class PackagesValidator
{
    public static function addPackageValidator($data) 
    {
        $rules = [
            'package_name' => 'required|string|unique:App\Models\Package,package_name|max:150|regex:/^[^\W]/',
            'package_code' => 'required|string|unique:App\Models\Package,package_code|max:150|regex:/^[^\W]/',
            'product_codes' => 'required|array|min:1',
            'product_codes.*' => 'required|string|exists:App\Models\Product,product_code|regex:/^\s*\S[A-Z0-9_]+$/',
            'status' => 'required|max:30',
        ];
        $customErrorMsg = [
            'product_codes.*.required' => 'The product code is required.',
            'product_codes.*.regex' => 'The product code format is invalid.',
            'product_codes.*.exists' => 'The selected product_code is invalid.',
            'package_name.regex' => 'The package name must not start with a special character.',
            'package_code.regex' => 'The package code must not start with a special character.',
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


    public static function updatePackageValidator($data) 
    {
        $rules = [
            'id' => 'required|alpha_dash|exists:App\Models\Package,package_uuid',
            'package_name' => 'required|string|max:150|regex:/^[^\W]/',
            'package_code' => 'required|string|max:150|regex:/^[^\W]/',
            'product_codes' => 'required|array|min:1',
            'product_codes.*' => 'required|string|exists:App\Models\Product,product_code|regex:/^\s*\S[A-Z0-9_]+$/',
            'status' => 'required|max:30',
        ];
        $customErrorMsg = [
            'product_codes.*.required' => 'The product code is required.',
            'product_codes.*.regex' => 'The product code format is invalid.',
            'product_codes.*.exists' => 'The selected product_code is invalid.',
            'package_name.regex' => 'The package name must not start with a special character.',
            'package_code.regex' => 'The package code must not start with a special character.',
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
