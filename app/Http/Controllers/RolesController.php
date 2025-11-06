<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Validators\RolesValidator;
use App\Services\RolesService;

class RolesController extends Controller
{
    public function roleList()
    {
        return view('role-list');
    }

    public function getRoles(Request $request)
    {
        $data = $request->all();
        
        $response = RolesService::getRolesData($data);
        $response = json_decode($response);

        return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
    }


    public function addRole(Request $request)
    {
        $data = $request->all();
        
        $validation = RolesValidator::addRoleValidator($data);
        if ($validation === true) 
        {
            $response = RolesService::addRoleData($data);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }


    public function updateRole(Request $request)
    {
        $data = $request->all();
        
        $validation = RolesValidator::updateRoleValidator($data);
        if ($validation === true) 
        {
            $response = RolesService::updateRoleData($data);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }
    
}
