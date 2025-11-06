<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PackagesService;
use App\Validators\PackagesValidator;
use App\Models\Package;

class PackagesController extends Controller
{
    public function packageList()
    {
        return view('package-list');
    }

    public function getPackages(Request $request)
    {
        $data = $request->all();

        $response = PackagesService::getPackagesData($data);
        $response = json_decode($response);

        return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
    }

    public function addPackage(Request $request)
    {
        $data = $request->all();

        $validation = PackagesValidator::addPackageValidator($data);
        if ($validation === true) 
        {
            $response = PackagesService::addPackageData($data);
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

    public function updatePackage(Request $request)
    {
        $data = $request->all();

        $validation = PackagesValidator::updatePackageValidator($data);
        if ($validation === true) 
        {
            $response = PackagesService::updatePackageData($data);
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

    public function deletePackage(Request $request)
    {
        $data = $request->all();

        $existPackage = Package::find($data['id']);

        if (!empty($existPackage))
        {
            $packages = Package::find($data['id'])->delete();
            return response()->json(["status" => true, "code" => 200, "message" => 'Package Deleted Successfully', "data" => $packages], 200);
        }
        else
        {
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => "", "status_code" => 422]);
        }
    }
}
