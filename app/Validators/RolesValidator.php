<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class RolesValidator
{
    public static function addRoleValidator($data) 
    {
        $validator = Validator::make($data, [
            'role_name' => 'required|string|unique:App\Models\Role,role_name|max:150|regex:/^[^\W]/',
            'role_code' => 'required|string|unique:App\Models\Role,role_code|max:150|regex:/^[^\W]/',
            'role_description' => 'nullable|string',
            'status' => 'required|string|max:50',
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


    public static function updateRoleValidator($data) 
    {
        $validator = Validator::make($data, [
            'id' => 'required|alpha_dash|exists:App\Models\Role,role_uuid,deleted_at,NULL',
            'role_name' => 'required|max:150|regex:/^[^\W]/',
            'role_code' => 'required|max:150|regex:/^[^\W]/',
            'role_description' => 'nullable|string',
            'status' => 'required|max:50',
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
   
}
