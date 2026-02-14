<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class UsersValidator
{
    public static function addUserValidator($data) 
    {
        $rules = [
            'first_name' => ['required', 'max:150', 'regex:/^[a-zA-Z\s\.]+$/i'],
            'last_name' => ['nullable', 'max:150', 'regex:/^[a-zA-Z\s\.]+$/i'],
            'user_name' => ['required', 'string', 'unique:App\Models\User,user_name', 'regex:/^[a-z0-9_]+$/' ,'max:150'],
            'user_type' => ['required', 'max:150', 'regex:/^[A-Z\_]+$/'],
            'email' => ['required', 'max:50', 'regex:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/'],
            'password' => [
                'required', 'string', 'min:8', 'max:30','regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                //'regex:/[@$!%*#?&]/'
            ],
            'confirm_password' => 'required|same:password',
            'phone' => ['required', 'regex:/^\+?[0-9]+$/'],
            'status' => 'required|max:50',
        ];
        $customErrorMsg = [
            'first_name.regex' => 'The first name must contain alphabets only.',
            'last_name.regex' => 'The last name must contain alphabets only.',
            'user_type.regex' => 'The user type must contain capital letters only.',
            'user_name.regex' => 'Smaller case letters, numbers, and underscore are allowed in the user name.'
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


    public static function updateUserValidator($data) 
    {
        $rules = [
            'id' => 'required|alpha_dash|exists:App\Models\User,user_uuid,deleted_at,NULL',
            'first_name' => ['required', 'max:150', 'regex:/^[a-zA-Z\s\.]+$/'],
            'last_name' => ['nullable', 'max:150', 'regex:/^[a-zA-Z\s\.]+$/'],
            'user_name' => ['required', 'string', 'regex:/^[a-z0-9_]+$/' ,'max:150'],
            'user_type' => ['required', 'max:150', 'regex:/^[A-Z\_]+$/'],
            'email' => ['required', 'max:50', 'regex:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/'],
            'phone' => ['required', 'regex:/^\+?[0-9]+$/'],
            'status' => 'required|max:50'
            /*,'password' => [
                'required', 'string', 'min:8', 'max:30','regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                //'regex:/[@$!%*#?&]/'
            ],
            'confirm_password' => 'required|same:password',*/
        ];
        //[AR-12-02-2026]
        if (isset($data['change_password']) && $data['change_password'] == 1) {
            $rules['password'] = [
                'required', 'string', 'min:8', 'max:30','regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/'
            ];
            $rules['confirm_password'] = 'required|same:password';
        }
        $customErrorMsg = [
            'first_name.regex' => 'The first name must contain alphabets only.',
            'last_name.regex' => 'The last name must contain alphabets only.',
            'user_type.regex' => 'The user type must contain capital letters only.',
            'user_name.regex' => 'Smaller case letters, numbers, and underscore are allowed in the user name.'
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
