<?php

namespace App\Http\Controllers;

use App\Exports\LicenseUserWiseExport;
use DB;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Models\ProductLicenseKeys;
use App\Services\LicenseKeyService;
use App\Validators\LicenseValidator;
use Maatwebsite\Excel\Facades\Excel;

class ProductLicenseKeysController extends Controller
{

    public static function cancel_and_refund_subscription(Request $request)
    {
        $data = $request->all();
        $user = $request->user();

        $validation = LicenseValidator::cancel_and_refund_subscription($data);
        if ($validation === true)
        {
            $response = LicenseKeyService::cancel_and_refund_subscription($data,$user);
            if (!empty($response['status'])) {
                return json_encode(["status" => true, "code" => 200, "message" => "License Key Renewal Successfully", "data" => $response['data'], "status_code" => 200]);
            } else {
                return response()->json(["status" => false, "code" => 500, "message" => "Failed to renew license key", "data" => []], 500);
            }
        } else {
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }
    public static function renew_existing_orders(Request $request)
    {
        $data = $request->all();
        $user = $request->user();

        $validation = LicenseValidator::renew_existing_orders($data);
        if ($validation === true)
        {
            $response = LicenseKeyService::renew_existing_orders($data,$user);
            if (!empty($response['status'])) {
                return json_encode(["status" => true, "code" => 200, "message" => "License Key Renewal Successfully", "data" => $response['data'], "status_code" => 200]);
            } else {
                return response()->json(["status" => false, "code" => 500, "message" => "Failed to renew license key", "data" => []], 500);
            }
        } else {
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }

    public function exportLicenseWithUser(Request $request)
    {
        $data = $request->all();
        // $data['type']='PACKAGE';
        // $packageList = LicenseKeyService::get_license_list($data, $user);
        // $result = $packageList;
        // dd($result);
        return Excel::download(new LicenseUserWiseExport($data), 'User-reports.xlsx');
    }

    public function licenseList()
    {
        return view('license-list');
        // return view('license-list-new');
    }

    public function licenseListV2()
    {
        return view('license-list-new');
    }

    public function purchaseReport()
    {
        return view('purchase-report-list');
    }

    public function expireReport()
    {
        return view('expire-report-list');
    }

    public function expiredLicenseList()
    {
        return view('expired-license-list');
    }

    public function generateLicenseKey(Request $request)
    {
        $data = $request->all();

        $validation = LicenseValidator::generateLicenseKeyValidator($data);
        if ($validation === true)
        {
            $response = LicenseKeyService::generate($data);
            $response = json_decode($response);
            //return $response;

            return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422,"message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }


    public function availabilityOfLicenseKey(Request $request)
    {
        $data = $request->all();
        $validation = LicenseValidator::validateLicenseKey($data);
        if ($validation === true)
        {
            $response = ProductLicenseKeys::where('license_key', $data['license_key'])->where('status', 'AVAILABLE')->first();

            if (!empty($response))
                return response()->json(["status" => true, "code" => 200,"message" => "Available", "data" =>  ["license_key" => ["status_code" => "AVAILABLE", "status_name" => "Available"]]], 200);
            else
                return response()->json(["status" => true, "code" => 200,"message" => "Not Available", "data" => ["license_key" => ["status_code" => "NOT_AVAILABLE", "status_name" => "Not Available"]]], 200);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422,"message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }


    public function licenseKeyValidation(Request $request)
    {
        $data = $request->all();
        $validation = LicenseValidator::validateLicenseKeyAndMac($data);
        if ($validation === true)
        {
            $response = LicenseKeyService::validateLicenseKey($data);
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


    public function licenseKeyDetailsUpdate(Request $request)
    {
        $data = $request->all();
        $validation = LicenseValidator::licenseKeyDetailsValidator($data);

        if ($validation === true)
        {
            $licenseKeyValidation = LicenseValidator::validateLicenseKey(['license_key' => $data['license_key']]);
            if ($licenseKeyValidation === true)
            {
                $response = LicenseKeyService::licenseKeyUpdateDetails($data);
                $response = json_decode($response);

                return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
            }
            else
            {
                //Return the error message
                $error = ["error" => $licenseKeyValidation->errors()->all()];
                return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
            }

        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }
    public function licenseActivationV2(Request $request)
    {
        $request_data = $request->all();
        $validation = LicenseValidator::licenseActivationValidation($request_data);
        if ($validation === true)
        {
            $license_activation_check = LicenseKeyService::licenseActivationCheck($request_data);
            if(!empty($license_activation_check)){
                $error = ["error" => $license_activation_check];
                return response()->json(["status" => false, "code" => 422, "message" => "Request Denied", "data" => $error], 422);
            } else {
                $license_activation = LicenseKeyService::licenseActivationV2($request_data);
                if(!empty($license_activation['error'])){
                    return response()->json(["status" => false, "code" => 422, "message" => "Request Denied", "data" => $license_activation], 422);
                } else {
                    return response()->json(["status" => false, "code" => 200, "message" => "License Key Has Been Activated Successfully", "data" => ['products'=>$license_activation['data']]], 200);
                }
            }
        } else {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
        }

    }


    public function getLicenseProducts(Request $request)
    {
        $data = $request->all();
        $result = LicenseKeyService::getLicenseProducts($data);
        return response()->json(["status" => false, "code" => 200, "message" => "Products list", "data" => ['products'=>$result]], 200);
    }
    public function getLicenses(Request $request)
    {
        $data = $request->all();
        $user = $request->user();
        // Default per page = 10
        $perPage = $request->get('per_page', 10);
        $response = LicenseKeyService::getLicensesData($data, $user, $perPage);
        $response = json_decode($response);

        if ($response->status)
            return response()->json(["status" => $response->status, "code" => $response->code, "message" => $response->message, "data" => $response->data], $response->status_code);
        else
            return response()->json(["status" => false, "code" => 422, "message" => 'License Keys Not Found', "data" => ''], 422);
    }
    public function listLicenses(Request $request)
    {
        $data = $request->all();
        $data['type']="PRODUCT";
        $user = $request->user();
        $perPage = $request->get('per_page', 10);
        $response = LicenseKeyService::listLicensesData($data, $user, $perPage);
        $response = json_decode($response);
        if ($response->status)
            return response()->json(["status" => $response->status, "code" => $response->code, "message" => $response->message, "data" => $response->data], $response->status_code);
        else
            return response()->json(["status" => false, "code" => 422, "message" => 'License Keys Not Found', "data" => ''], 422);
    }
    public function listPackageLicenses(Request $request)
    {
        $data = $request->all();
        // $data['type']="PACKAGE";
        $user = $request->user();
        $perPage = $request->get('per_page', 10);
        $response = LicenseKeyService::get_license_list($data, $user, $perPage);
        if ($response)
            return response()->json(["status" => 'Sucess', "code" => 200, "message" => 'List package licesens', "data" => $response], 200);
        else
            return response()->json(["status" => false, "code" => 422, "message" => 'License Keys Not Found', "data" => ''], 422);
    }


    public function getLicenseDetails(Request $request)
    {
        $data = $request->all();
        $user = $request->user();
        $validation = LicenseValidator::idValidator($data);

        if ($validation === true)
        {
            $response = LicenseKeyService::getLicenseDetailsData($data, @$user);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->code, "message" => $response->message, "data" => $response->data], $response->status_code);

        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }


    public function getActualLicense(Request $request)
    {
        $data = $request->all();

        $validation = LicenseValidator::idPasswordValidator($data);
        if ($validation === true)
        {
            $user = $request->user();

            $response = LicenseKeyService::getActualLicenseData($data, $user);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }


    public function resetMacAddress(Request $request)
    {
        $data = $request->all();

        $validation = LicenseValidator::idValidator($data);
        if ($validation === true)
        {
            $user = $request->user();

            $response = LicenseKeyService::resetMacAddressProcess($data, $user);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }

    public function resetMacAddressV2(Request $request)
    {
        $data = $request->all();
        $user = $request->user();
        $validation = LicenseValidator::resetLicenseValidator($data);
        if ($validation === true)
        {
            $response = LicenseKeyService::licenseResetProcessV2($data, $user);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422,"message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }


    public static function getProductBasedLicenseCount(Request $request)
    {
        $data = $request->all();

        $response = LicenseKeyService::getProductBasedLicenseCountData($data);
        $response = json_decode($response);

        return response()->json(["status" => $response->status, "code" => $response->code, "message" => $response->message, "data" => $response->data], $response->status_code);
    }


    public static function licenseDeactivate(Request $request)
    {
        $data = $request->all();
        $user = $request->user();

        $validation = LicenseValidator::licenseDeactivateValidator($data);
        if ($validation === true)
        {
            $response = LicenseKeyService::licenseDeactivateProcess($data, @$user);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422,"message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }


    public static function licenseActivate(Request $request)
    {
        $data = $request->all();
        $user = $request->user();

        $validation = LicenseValidator::licenseActivateValidator($data);
        if ($validation === true)
        {
            $response = LicenseKeyService::licenseActivateProcess($data, $user);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422,"message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }


    public static function deleteLicenseKey(Request $request)
    {
        $data = $request->all();

        $validation = LicenseValidator::licenseDeleteValidator($data);
        if ($validation === true)
        {
            $response = LicenseKeyService::deleteLicensekeyProcess($data);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422,"message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }


    public static function getLicenseHistory(Request $request)
    {
        $data = $request->all();

        $validation = LicenseValidator::idValidator($data);
        if ($validation === true)
        {
            $response = LicenseKeyService::getLicenseHistoryData($data);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422,"message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }


    public static function licenseRenewal(Request $request)
    {
        $data = $request->all();
        $user = $request->user();

        $validation = LicenseValidator::licenseRenewalValidator($data);
        if ($validation === true)
        {
            $response = LicenseKeyService::licenseRenewalProcess($data, @$user);
            $response = json_decode($response);

            return response()->json(["status" => $response->status, "code" => $response->code, "message" => $response->message, "data" => $response->data], $response->status_code);
        }
        else
        {
            //Return the error message
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422,"message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }

    public static function renew_existing_license_key(Request $request)
    {
        $data = $request->all();
        $user = $request->user();

        $validation = LicenseValidator::renew_existing_license_key($data);
        if ($validation === true)
        {
            $response = LicenseKeyService::renew_existing_license_key($data,$user);
            if ($response) {
                $order_item_info=OrderService::view_order_items(@$data['order_item_id']);
                return json_encode(["status" => true, "code" => 200, "message" => "License Key Renewal Successfully", "data" => $order_item_info, "status_code" => 200]);
            } else {
                return response()->json(["status" => false, "code" => 500, "message" => "Failed to renew license key", "data" => []], 500);
            }
        } else {
            $error = ["error" => $validation->errors()->all()];
            return response()->json(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error], 422);
        }
    }
}
