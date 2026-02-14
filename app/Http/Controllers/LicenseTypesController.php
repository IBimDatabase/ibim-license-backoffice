<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LicenseType;
use App\Services\LicenseTypeService;
use App\Validators\LicenseTypeValidator;
use App\Exports\LicenseTypesExport;
use Maatwebsite\Excel\Facades\Excel;

class LicenseTypesController extends Controller
{
    public function licenseTypeList()
    {
        return view('license-type-list');
    }


    public function viewLicenseTypes(Request $request, $id)
    {
        $data = $request->all();

        $response = LicenseTypeService::getType($data, $id);
        $response = json_decode($response);

        return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);   
    }
    public function getLicenseTypes(Request $request)
    {
        $data = $request->all();
        // Default per page = 10
        $perPage = $request->get('per_page', 10);
        $response = LicenseTypeService::getType($data, null, $perPage);
        $response = json_decode($response);

        return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);   
    }


    public function addLicenseType(Request $request)
    {
        $data = $request->all();
        $validation = LicenseTypeValidator::addLicenseTypeValidator($data);

        if ($validation === true) 
        {
            $response = LicenseTypeService::addType($data);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422,"message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }


    public function updateLicenseType(Request $request)
    {
        $data = $request->all();
        $validation = LicenseTypeValidator::updateLicenseTypeValidator($data);

        if ($validation === true) 
        {
            $response = LicenseTypeService::updateType($data);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422,"message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }


    public function importLicenseType(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', -1);

        $data = $request->all();
        $validation = LicenseTypeValidator::importLicenseTypeValidator($data);
        if ($validation === true) 
        {
            $response = LicenseTypeService::importLicenseTypeData($request->file('importedFile'));
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


    public function exportLicenseType(Request $request)
    {
        return Excel::download(new LicenseTypesExport, 'licenseType.xlsx');
    }

    public function deleteLicenseType(Request $request)
    {
        $data = $request->all();
        $validation = LicenseTypeValidator::deleteLicenseTypeValidator($data);

        if ($validation === true) 
        {
            $response = LicenseTypeService::deleteLicenseType($data);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422,"message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }
}
