<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class LicenseTypeValidator
{
    public static function addLicenseTypeValidator($data) 
    {
        $rules = [
            'name' => 'required|unique:App\Models\LicenseType,name|max:150|regex:/^[^\W]/',
            'code' => 'required|unique:App\Models\LicenseType,code|max:150|regex:/^[^\W]/',
            'expiry_period' => 'required|string|max:150',
            'expiry_duration' => 'nullable|required_if:expiry_period,!=,date|numeric|min:1',
            'expiry_duration_date' => 'nullable|required_if:expiry_period,=,date|date|after:today|date_format:d-m-Y',
            'description' => 'nullable|string',
            'status' => 'required|max:50',
        ];
        $customErrorMsg = [
            'name.unique' => 'The license type has already been taken.',
            'code.unique' => 'The license code has already been taken.',
            'name.regex' => 'The name must not start with a special character.',
            'code.regex' => 'The code must not start with a special character.',
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


    public static function updateLicenseTypeValidator($data) 
    {
        $rules = [
            'id' => 'required|numeric',
            'name' => 'required|max:150|regex:/^[^\W]/',
            'code' => 'required|max:150|regex:/^[^\W]/',
            'expiry_period' => 'required|string|max:150',
            'expiry_duration' => 'nullable|required_if:expiry_period,!=,date|numeric|min:1',
            'expiry_duration_date' => 'nullable|required_if:expiry_period,=,date|date|after:today|date_format:d-m-Y',         
            'description' => 'nullable|string',
            'status' => 'required|max:50',
        ];
        $customErrorMsg = [
            'name.regex' => 'The name must not start with a special character.',
            'code.regex' => 'The code must not start with a special character.',
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


    public static function importLicenseTypeValidator($data) 
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

    public static function deleteLicenseTypeValidator($data) 
    {
        $rules = [
            'id' => 'required|numeric|exists:App\Models\LicenseType,id,deleted_at,NULL',
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
