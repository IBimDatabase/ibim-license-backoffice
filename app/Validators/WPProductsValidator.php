<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class WPProductsValidator
{
    public static function createProductValidator($data) 
    {
        $rules = [
            'name' => 'required|string|max:255|regex:/^[^\W]/',
            'status' => 'required|string|max:30|regex:/^[^\W]/',
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


    public static function updateProductValidator($data) 
    {
        $rules = [
            'id' => 'required|numeric',
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


    public static function deleteProductValidator($data) 
    {
        $rules = [
            'id' => 'required|numeric',
            'force' => 'required|boolean',
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


    public static function sendProductInfoValidator($data) 
    {
        $rules = [
            'id' => 'required|numeric',
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
