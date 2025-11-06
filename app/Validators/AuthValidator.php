<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class AuthValidator
{
    public static function registerValidator($data) 
    {
        $validator = Validator::make($data, [
            'license_code' => 'required|max:30',
            'product_code' => 'required|max:255',
            'counts' => 'nullable',
        ]);

        if ($validator->fails()) 
        {
            return $validator->errors();
        }
        else 
        {
            return true;
        }
    }
    

    public static function loginValidator($data)
    {
        $validator = Validator::make($data, [
            'user_name' => 'required|string|max:255',
            'password' => 'required|string',
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


    public static function changePasswordValidator($data)
    {
        $rules = [
            'old_password' => 'required|string|password',
            'new_password' => [
                'required', 'string', 'min:8', 'max:30','regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                //'regex:/[@$!%*#?&]/'
            ],
            'confirm_password' => 'required|same:new_password',
        ];

        $customErrorMsg = [
            'old_password.password' => 'The old password does not match.'
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
