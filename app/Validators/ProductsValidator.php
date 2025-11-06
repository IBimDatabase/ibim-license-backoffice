<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class ProductsValidator
{
    public static function addProductValidator($data) 
    {
        $rules = [
            'product_name' => 'required|string|unique:App\Models\Product,product_name|max:150|regex:/^[^\W]/',
            'product_code' => 'required|string|unique:App\Models\Product,product_code|max:150|regex:/^[^\W]/',
            'product_prefix' => 'required|string|max:150',
            'description' => 'nullable|string',
            'status' => 'required|max:30',
        ];
        $customErrorMsg = [
            'product_name.regex' => 'The product name must not start with a special character.',
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


    public static function updateProductValidator($data) 
    {
        $rules = [
            'id' => 'required|alpha_dash|exists:App\Models\Product,product_uuid,deleted_at,NULL',
            'product_name' => 'required|string|max:150|regex:/^[^\W]/',
            'product_prefix' => 'required|string|max:150|regex:/^[^\W]/',
            'description' => 'nullable|string',
            'status' => 'required|max:30',
        ];
        $customErrorMsg = [
            'product_name.regex' => 'The product name must not start with a special character.',
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


    public static function importProductValidator($data) 
    {
        $rules = [
            'importedFile' => 'required|file|mimes:xls,xlsx'
        ];
        $customErrorMsg = [
            'importedFile.mimes' => 'The imported file must be a Excel file (.xls / .xlsx).',
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

    public static function syncWooCommerceProductValidator($data) 
    {
        $rules = [
            'id' => 'required|alpha_dash|exists:App\Models\Product,product_uuid,deleted_at,NULL',
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
   
}
