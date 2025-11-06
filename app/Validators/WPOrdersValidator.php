<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class WPOrdersValidator
{
    public static function createOrderValidator($data) 
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


    public static function updateOrderValidator($data) 
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


    public static function deleteOrderValidator($data) 
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


    public static function sendOrderInfoValidator($data) 
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
