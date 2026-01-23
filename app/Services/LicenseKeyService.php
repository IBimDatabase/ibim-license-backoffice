<?php

namespace App\Services;

use Log;
use App\Models\User;
use App\Models\Order;
use App\Models\Package;
use App\Models\Product;
use App\Models\Customer;
//use Webpatser\Uuid\Uuid;
//use GoldSpecDigital\LaravelUuid\Uuid; // Added_by_Abdul_Rehman_for_Upgrade Laravel
use App\Models\OrderItem;
use App\Helpers\AppHelper;
use App\Models\LicenseType;
use App\Models\LicenseAudit;
use App\Models\LicenseProduct;
use App\Helpers\LicenseKeyHelper;
use App\Models\ProductLicenseKeys;
use Illuminate\Support\Facades\DB;
DB::enableQueryLog();
use Illuminate\Support\Facades\Hash;
use App\Helpers\UserSystemInfoHelper;

class LicenseKeyService
{
    CONST  SHOW_LICENSE_KEY = ['superadmin@ibim.com', 'mani@appyhub.com'];

    public static function cancel_and_refund_subscription($requested_data)
    {
        $result = [
            'status' => false,
            'data' => null,
        ];

        $order_info = DB::table('orders')
        ->where('order_uuid', $requested_data['order_id'])
        ->whereNull('deleted_at')
        ->first();
        if (empty($order_info)) {
            return $result;
        }
        $licenses_with_info = DB::table('product_license_keys as plk')
        ->leftJoin('license_types as lt', 'plk.license_type', '=', 'lt.code')
        ->leftJoin('products as p', function ($join) {
            return $join->on('plk.product_id', 'p.id');
        })->leftJoin('packages as pk', function ($join) {
            return $join->on('plk.package_id', 'pk.id');
        })
            ->where('plk.order_id', $order_info->id)
            ->where(function ($query) use ($requested_data) {
                foreach ($requested_data['entity_item_id'] as $entity) {
                    if ($entity['type'] === 'PRODUCT') {
                        $query->orWhere('p.product_uuid', $entity['id']);
                    } elseif ($entity['type'] === 'PACKAGE') {
                        $query->orWhere('pk.package_uuid', $entity['id']);
                    }
                }
            })
            ->whereNull('plk.deleted_at')
            ->select('plk.id', 'plk.license_type', 'lt.expiry_duration')
            ->get();
        if (empty($licenses_with_info)) {
            return $result;
        }

        $license_updated = [];
        foreach ($licenses_with_info as $license) {
            if ($license->expiry_duration) {
                $update_result = DB::table('product_license_keys')
                ->where('id', $license->id)
                    ->update([
                        'expiry_date' => date('Y-m-d H:i:s'),
                        'status' => 'EXPIRED'
                    ]);

                if ($update_result) {
                    $license_updated[] = $license->id;
                }
            }
        }

        if (!empty($license_updated)) {
            $result['status'] = true;
            $view_payload = ['id' => $order_info->order_uuid];
            $result['data'] = OrderService::view_order_data($view_payload);
        }

        return $result;
    }
    public static function renew_existing_orders($requested_data)
    {
        $result = [
            'status' => false,
            'data' => null,
        ];

        $order_info = DB::table('orders')
        ->where('order_uuid', $requested_data['order_id'])
        ->whereNull('deleted_at')
        ->first();

        if (empty($order_info)) {
            return $result;
        }

        // Fetch applicable licenses with license type info in a single query
        $licenses_with_info = DB::table('product_license_keys as plk')
        ->leftJoin('license_types as lt', 'plk.license_type', '=', 'lt.code')
        ->leftJoin('products as p', function ($join) {
            return $join->on('plk.product_id', 'p.id');
        })->leftJoin('packages as pk', function ($join) {
            return $join->on('plk.package_id', 'pk.id');
        })
            ->where('plk.order_id', $order_info->id)
            ->where(function ($query) use ($requested_data) {
                foreach ($requested_data['entity_item_id'] as $entity) {
                    if ($entity['type'] === 'PRODUCT') {
                        $query->orWhere('p.product_uuid', $entity['id']);
                    } elseif ($entity['type'] === 'PACKAGE') {
                        $query->orWhere('pk.package_uuid', $entity['id']);
                    }
                }
            })
            ->whereNull('plk.deleted_at')
            ->select('plk.id', 'plk.license_type', 'lt.expiry_duration')
            ->get();

        if (empty($licenses_with_info)) {
            return $result;
        }

        $license_updated = [];
        foreach ($licenses_with_info as $license) {
            if ($license->expiry_duration) {
                $new_expiry_date = self::generateExpiryDate($license->expiry_duration, null); // Generate new expiry date

                $update_result = DB::table('product_license_keys')
                ->where('id', $license->id)
                    ->update([
                        'purchased_date' => date('Y-m-d H:i:s'),
                        'expiry_date' => $new_expiry_date,
                        'status' => 'PURCHASED'
                    ]);

                if ($update_result) {
                    $license_updated[] = $license->id;
                }
            }
        }

        if (!empty($license_updated)) {
            $result['status'] = true;
            $view_payload = ['id' => $order_info->order_uuid];
            $result['data'] = OrderService::view_order_data($view_payload);
        }

        return $result;
    }


    public static function generate($data)
    {
        $productLicenseKeys = [];
        $productOrPackage = json_decode(json_encode($data['product_code']));
        $customer = null;

        $licenseType = LicenseType::where([
            'code' => $data['license_code'],
            'status' => 'AVAILABLE'
        ])->first();

        if ($productOrPackage->type == 'PRODUCT_CODE')
        {
            $product = Product::where([
                'product_code' => $productOrPackage->value,
                'status' => 'ACTIVE'
            ])->first();

            if (empty($product))
            {
                return json_encode(["status" => false, "code" => 422,"message" => "Data Not Found", "data" => ["error" => ["The given product code is not found"]], "status_code" => 422]);
            }
        }
        else if ($productOrPackage->type == 'PACKAGE')
        {
            $package = Package::where([
                'package_code' => $productOrPackage->value,
                'status' => 'AVAILABLE'
            ])->first();

            if (empty($package))
            {
                return json_encode(["status" => false, "code" => 422, "message" => "Data Not Found", "data" => ["error" => ["The given package is not found"]], "status_code" => 422]);
            }
        }
        else
        {
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => "", "status_code" => 422]);
        }

        if (empty($licenseType))
        {
            return json_encode(["status" => false, "code" => 422, "message" => "Data Not Found", "data" => ["error" => ["The given license code is not found"]], "status_code" => 422]);
        }
        else if ($licenseType->duration_type == 'DATE' && date('d-m-Y', strtotime($licenseType->expiry_duration)) <= date('d-m-Y') )
        {
            return json_encode(["status" => false, "code" => 422, "message" => "Request Denied", "data" => ["error" => ["The given license code is invalid"]], "status_code" => 422]);
        }

        $orderData = [
            'order_source' => (key_exists('order_source', $data)) ? $data['order_source'] : '',
            'order_reference_no' => (key_exists('order_reference_no', $data)) ? $data['order_reference_no'] : '',
            'order_info' => (key_exists('order_info', $data)) ? $data['order_info'] : '',
            'order_placed_at' => (key_exists('order_time', $data)) ? ( (strtotime($data['order_time']) > 0) ? date('Y-m-d H:i:s', strtotime($data['order_time'])) : date('Y-m-d H:i:s') ) : date('Y-m-d H:i:s'),
            'source' => 'BACK_OFFICE',
            'status' => 'PROCESSING',
        ];

        $order = Order::insertRecord($orderData);

        
        if (!empty(@$data['email']) || !empty(@$data['phone_no']))
        {
            $customerDetails = [
                'first_name' => (key_exists('first_name', $data)) ? $data['first_name'] : '',
                'last_name' => (key_exists('last_name', $data))? $data['last_name'] : '',
                'email' => (key_exists('email', $data))? $data['email'] : '',
                'phone_no' => (key_exists('phone_no', $data))? $data['phone_no'] : '',
            ];
            $customer = LicenseKeyService::saveCustomerDetails($customerDetails);
        }
        $i = 1;
        do {
            // Make sure avoid LicenseKey duplication
            do {
                $newLicenseKey = LicenseKeyHelper::create();
                $checkExistence = ProductLicenseKeys::where('license_key', $newLicenseKey)->first();

                if (empty($checkExistence))
                    $newLicenseKey = $newLicenseKey;
                else
                    $newLicenseKey = '';

            } while (empty($newLicenseKey));

            if (!empty($product))
            {
                $expiry_date=null;
                $licenseProductData = [
                    'type_id' => $licenseType->id,
                    'product_id' => $product->id,
                    'duration_type' => $licenseType->duration_type,
                    'expiry_duration' => $licenseType->expiry_duration,
                    'status' => 'AVAILABLE',
                ];
                $licenseProducts = LicenseProduct::insertRecord($licenseProductData);

                $expiryDate = self::generateExpiryDate($licenseProducts->expiry_duration, $expiry_date);


                $productLicenseKeysData = [
                    'license_type_id' => $licenseProducts->id,
                    'license_uuid' => Uuid::generate(4),
                    'product_id' => $product->id,
                    'license_type' => $licenseType->code,
                    'license_key' => $newLicenseKey,
                    'order_id' => (isset($order)) ? $order->id : NULL,
                    'customer_id' => (!empty($customer)) ? $customer->id : NULL,
                    'status' => 'AVAILABLE',
                    'expiry_date' =>  $expiryDate
                    //'created_by' => auth()->user()->id,
                ];
                $productLicenseKeysModel = ProductLicenseKeys::insertRecord($productLicenseKeysData);
                $productLicenseKeysModel = ProductLicenseKeys::find($productLicenseKeysModel->id);

                $licenseKeyModel = LicenseKeyService::getLicenseRelationalData($productLicenseKeysModel, false);
                $productLicenseKeys[] = $licenseKeyModel;
            }
            else if (!empty($package))
            {
                $productCodes = json_decode($package->product_codes);

                foreach ($productCodes as $productCode)
                {
                    $getProduct = Product::where([
                        'product_code' => $productCode,
                        'status' => 'ACTIVE'
                    ])->first();

                    if (!empty($getProduct))
                    {
                        $licenseProductData = [
                            'type_id' => $licenseType->id,
                            'product_id' => $getProduct->id,
                            'package_id' => $package->id,
                            'duration_type' => $licenseType->duration_type,
                            'expiry_duration' => $licenseType->expiry_duration,
                            "status" => 'AVAILABLE',
                            //'created_by' => auth()->user()->id,
                        ];

                        $licenseProducts = LicenseProduct::insertRecord($licenseProductData);
                        $expiryDate = self::generateExpiryDate($licenseProducts->expiry_duration, $expiry_date=null);


                        $productLicenseKeysData = [
                            'license_type_id' => $licenseProducts->id,
                            'license_uuid' => Uuid::generate(4),
                            'product_id' => $getProduct->id,
                            'package_id' => $package->id,
                            'license_type' => $licenseType->code,
                            'license_key' => $newLicenseKey,
                            'order_id' => (isset($order)) ? $order->id : NULL,
                            'customer_id' => (!empty($customer)) ? $customer->id : NULL,
                            'status' => 'AVAILABLE',
                            'expiry_date' => $expiryDate
                            //'created_by' => auth()->user()->id,
                        ];
                        $productLicenseKeysModel = ProductLicenseKeys::insertRecord($productLicenseKeysData);
                        $productLicenseKeysModel = ProductLicenseKeys::find($productLicenseKeysModel->id);

                        $licenseKeyModel = LicenseKeyService::getLicenseRelationalData($productLicenseKeysModel, false);
                        $productLicenseKeys[] = $licenseKeyModel;
                    }
                }
            }
            $i++;

        } while ( (key_exists('counts', $data)) ? $data['counts'] >= $i : false);

        if ($productLicenseKeys)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "License Key Generated Successfully", "data" => $productLicenseKeys, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500]);
        }

    }


    public static function validateLicenseKey($data) {
        $product = Product::where('product_code', $data['product_code'])->first();

        if (empty($product))
        {
            return json_encode(["status" => true, "code" => 200, "message" => "Not Available", "data" => ["license_key" => ["status_code" => "NOT_AVAILABLE", "status_name" => "Not Available"]], "status_code" => 200]);
        }

        $productLicenseKey = ProductLicenseKeys::where([
                'license_key' =>  $data['license_key'],
                'mac_address' =>  $data['mac_address'],
                'product_id' =>  $product->id,
            ])->whereNotIn('status', ['DEACTIVATED'])->first();

        if (!empty($productLicenseKey))
        {
            if (strtotime($productLicenseKey->expiry_date) > 0 && date('Y-m-d H:i:s', strtotime($productLicenseKey->expiry_date)) < date('Y-m-d H:i:s') && $productLicenseKey->status != 'DEACTIVATED')
            {
                $productLicenseKeysData = [
                    'status' => 'EXPIRED'
                ];

                ProductLicenseKeys::updateRecord($productLicenseKeysData, $productLicenseKey->id);
                return json_encode(["status" => true, "code" => 200, "message" => "Expired License Key", "data" => ["license_key" => ["status_code" => "EXPIRED", "status_name" => "Expired"]], "status_code" => 200]);
            }
            else if ($productLicenseKey->status == 'DEACTIVATED')
            {
                return json_encode(["status" => true, "code" => 200, "message" => "Deactivated License Key", "data" => ["license_key" => ["status_code" => "DEACTIVATED", "status_name" => "Deactivated"]], "status_code" => 200]);
            }
            else
            {
                return json_encode(["status" => true, "code" => 200, "message" => "Active License Key", "data" => ["license_key" => ["status_code" => "ACTIVE", "status_name" => "Active"]], "status_code" => 200]);
            }
        }
        else
        {
            return json_encode(["status" => true, "code" => 200, "message" => "Not Available", "data" => ["license_key" => ["status_code" => "NOT_AVAILABLE", "status_name" => "Not Available"]], "status_code" => 200]);
        }
    }
    public static function licenseActivationCheck($request_data)
    {
        $errors=[];
        if (@$request_data['license_key'] == config('app.trial_license_key')){
            $license_products_query=DB::table('product_license_keys as plk')
            ->join('products as p', 'plk.product_id', 'p.id')
            ->leftJoin('packages as pa', 'plk.package_id', 'pa.id')
            ->select('plk.*', 'p.product_code', 'pa.package_code')
            ->whereNull('plk.deleted_at');
            if(!empty($request_data['type']) && strtoupper($request_data['type'])=='PRODUCT'){
                $license_products_query->where('p.product_code', @$request_data['product_code']);
            }
            if(!empty($request_data['type']) && strtoupper($request_data['type'])=='PACKAGE'){
                $license_products_query->where('pa.package_code', @$request_data['package_code']);
            }
            $license_products_query->where('plk.mac_address', @$request_data['mac_address']);
            $license_products_query->where('plk.license_key', @$request_data['license_key']);
            // dd($license_products_query->toSql());
            $trial_licence_data_exist=$license_products_query->first();
            if(!empty($trial_licence_data_exist)){
                $errors[]="Trial License is already activated.";
            }
        } else {
            $license_data=[];
            $license_data['license_key']=@$request_data['license_key'];
            $license_product = LicenseKeyService::getLicenseProducts($license_data);
            $activated=0;
            $avaliable=0;
            $product_exist=false;
            $package_exist=false;
            if(!empty($license_product)){
                
                foreach ($license_product as $key => $value) {
                    if(!empty($value['status']) && in_array($value['status'], ['PURCHASED'])){
                        if($value['mac_address']!=@$request_data['mac_address']){
                            if(!in_array("License is already activated.", $errors)){
                                $errors[]="License is already activated.";
                            }
                        }
                        $activated=$activated+1;
                    }
                    if(!empty($value['status']) && in_array($value['status'], ['AVAILABLE'])){
                        $avaliable=$avaliable+1;
                    }
                    if(!empty($request_data['type']) && strtoupper($request_data['type'])=='PRODUCT'){
                        if($request_data['product_code']==$value['product_code']){
                            $product_exist=true;
                        }
                    }
                    if(!empty($request_data['type']) && strtoupper($request_data['type'])=='PACKAGE'){
                        if($request_data['package_code']==$value['package_code']){
                            $package_exist=true;
                        }
                    }
                }
            }
            if(!empty($request_data['type']) && strtoupper($request_data['type'])=='PRODUCT' && empty($product_exist)){
                $errors[]="Product code is not associated with given License key";
            }
            if(!empty($request_data['type']) && strtoupper($request_data['type'])=='PACKAGE' && empty($package_exist)){
                $errors[]="Package code is not associated with given License key.";
            }
        }
        
        return $errors;
    }
    public static function licenseActivationV2($request_data)
    {
        $system_info = [
            "BROWSER" => UserSystemInfoHelper::get_browsers(),
            "OS" => UserSystemInfoHelper::get_os(),
            "IP_ADDRESS" => UserSystemInfoHelper::get_ip(),
        ];
        $result=[];
        $result['data']=[];
        if(!empty($request_data['license_key'])){
            $license_products_query=DB::table('product_license_keys as plk')
            ->join('products as p', 'plk.product_id', 'p.id')
            ->leftJoin('packages as pa', 'plk.package_id', 'pa.id')
            ->select('plk.*', 'p.product_code', 'pa.package_code')
            ->whereNull('plk.deleted_at');
            
            $is_license_product_already_activated_query = (clone $license_products_query);

            if(!empty($request_data['type']) && strtoupper($request_data['type'])=='PRODUCT'){
                $license_products_query->where('p.product_code', @$request_data['product_code']);
            }
            if(!empty($request_data['type']) && strtoupper($request_data['type'])=='PACKAGE'){
                $license_products_query->where('pa.package_code', @$request_data['package_code']);
            }
            $mac_address=@$request_data['mac_address'];
            if (@$request_data['license_key'] == config('app.trial_license_key')){
                $license_products_query->where('plk.mac_address', @$request_data['mac_address']);
                $trial_licence_data_exist=$license_products_query->where('plk.license_key', @$request_data['license_key'])->first();
                $license_type_query = DB::table('license_types as lt')
                    ->leftJoin('license_products as lp', 'lp.type_id', 'lt.id')->whereNull('lt.deleted_at')->where('lt.status', 'AVAILABLE')
                    ->leftJoin('products as p', 'p.id', 'lp.product_id')
                    ->leftJoin('packages as pa', 'pa.id', 'lp.package_id')
                    ->where('lt.code', 'TRIAL')
                    ->select('lt.*', 'lp.id as product_license_type_id', 'lp.package_id', 'lp.product_id', 'lp.expiry_duration', 'lp.duration_type');
                
                if(!empty($request_data['type']) && strtoupper($request_data['type'])=='PRODUCT'){
                    $license_type_query->where('p.product_code', @$request_data['product_code']);
                } else if(!empty($request_data['type']) && strtoupper($request_data['type'])=='PACKAGE'){
                    $license_type_query->where('pa.package_code', @$request_data['package_code']);
                } else {
                    $license_type_query->whereNull('p.product_code')->whereNull('pa.package_code');
                }
                $license_type_exist = $license_type_query->get();
                if(!empty($license_type_exist) && count($license_type_exist)>0){
                    foreach ($license_type_exist as $key => $value) {
                        $create_license_data = [
                            'license_type_id' => @$value->product_license_type_id,
                            'license_type' => 'TRIAL',
                            'entity_type' => 'PRODUCT',
                            'product_id' => @$value->product_id,
                            'package_id' => @$value->package_id,
                            'license_key' => config('app.trial_license_key'),
                            'mac_address' => $mac_address,
                            'expiry_date' => self::generateExpiryDate(@$value->expiry_duration),
                            'purchased_date' => date('Y-m-d H:i:s'),
                            'status' => 'PURCHASED',
                        ];
                        $updated_license_info=ProductLicenseKeys::insertRecord($create_license_data);
                        $licenseAuditData = [
                            'license_id' => $updated_license_info->id,
                            'license_audit_uuid' => Uuid::generate(4),
                            'entry_type' => 'LICENSE_ACTIVATION',
                            'license_key' => $updated_license_info->license_key,
                            'mac_address' => $updated_license_info->mac_address,
                            'system_info' => json_encode($system_info),
                        ];
                        LicenseAudit::insertRecord($licenseAuditData);
                    }
                    $license_data=[];
                    $license_data['license_key']=@$request_data['license_key'];
                    $license_data['mac_address']=$mac_address;
                    $result['data'] = LicenseKeyService::getLicenseProducts($license_data);
                } else {
                    $result=[];
                    $result['error']=[
                        "Trial License is not avaliable."
                    ];
                }
            } else {
                $todayDate = date('Y-m-d H:i:s');
                // $license_products_query->leftJoin('packages as pa', 'plk.package_id', 'pa.id');
                $license_products_query->leftJoin('license_types as lt', 'lt.code', 'plk.license_type');
                $license_products_query->select('plk.*', 'p.product_code', 'pa.package_code', 'lt.duration_type', 'lt.expiry_duration');                
                
                $license_data_exist=$license_products_query->where('plk.license_key', @$request_data['license_key'])->get();

                
                $is_license_product_already_activated_query->leftJoin('license_types as lt', 'lt.code', 'plk.license_type');
                $is_license_product_already_activated_query->select('plk.*', 'p.product_code', 'pa.package_code', 'lt.duration_type', 'lt.expiry_duration')->whereNotNull('plk.expiry_date')->where('plk.license_key', @$request_data['license_key'])->orderBy('plk.expiry_date','asc');

                $expiry_date_exist = null;
                $purchase_date_exist = null;  
                // dd($license_data_exist, $is_license_product_already_activated, $expiry_date_exist);
                if(!empty($license_data_exist)){
                    foreach ($license_data_exist as $key => $value) {
                        $is_license_product_already_activated = $is_license_product_already_activated_query->where('plk.order_id', $value->order_id)->first();
                        if(!empty($value->status) && in_array($value->status, ['AVAILABLE'])){
                            $license_info=[];
                            $license_info['mac_address']=(!empty($mac_address)) ? $mac_address : $value->mac_address;
                            if (!empty(@$is_license_product_already_activated)) {
                                $expiry_date_exist = @$is_license_product_already_activated->expiry_date;
                                // $purchase_date_exist = @$is_license_product_already_activated->purchased_date;
                            }
                            if(empty($value->expiry_date)){
                                // if(!empty($value->purchased_date)){
                                //     $license_info['expiry_date']=self::generateExpiryDate(@$value->expiry_duration, $value->purchased_date);
                                    // $license_info['expiry_date']=self::generateExpiryDate(@$value->expiry_duration, $value->created_at);
                                // } else {
                                    // $license_info['expiry_date']=(!empty($expiry_date_exist)) ? $expiry_date_exist : self::generateExpiryDate(@$value->expiry_duration);
                                    $license_info['expiry_date']=(!empty($expiry_date_exist)) ? $expiry_date_exist : self::generateExpiryDate(@$value->expiry_duration, $value->created_at);
                                // }
                            } 
                            if(!empty($value->purchased_date)){
                                $license_info['purchased_date']=$value->purchased_date;
                            } else {
                                $license_info['purchased_date']= (!empty(@$purchase_date_exist)) ? @$purchase_date_exist : $todayDate;
                            }
                            $license_info['status']='PURCHASED';
                            $updated_license_info=ProductLicenseKeys::updateRecord($license_info, $value->license_uuid);
                            
                            $licenseAuditData = [
                                'license_id' => $updated_license_info->id,
                                'license_audit_uuid' => Uuid::generate(4),
                                'entry_type' => 'LICENSE_ACTIVATION',
                                'license_key' => $updated_license_info->license_key,
                                'mac_address' => $updated_license_info->mac_address,
                                'system_info' => json_encode($system_info),
                            ];
                            LicenseAudit::insertRecord($licenseAuditData);
                        }
                    }
                
                }
                $license_data=[];
                $license_data['license_key']=@$request_data['license_key'];
                $result['data'] = LicenseKeyService::getLicenseProducts($license_data);
            }
        }
        return $result;

    }

    public static function licenseKeyUpdateDetails($data)
    {
        if (key_exists('product_code', $data))
        {
            $product = Product::where('product_code', $data['product_code'])->first();

            if (empty($product))
            {
                Log::debug("License Activation Failure: The given product is not found, Input Data: ".json_encode($data));

                return json_encode(["status" => true, "code" => 200, "message" => "Data Not Found", "data" => ["error" => ["The given product is not found"]], "status_code" => 200]);
            }
        }

        if ($data['license_key'] == config('app.trial_license_key'))
        {
            $existProductLicenseKey = ProductLicenseKeys::where([
                'license_key' => $data['license_key'],
                'mac_address' => $data['mac_address'],
                'product_id' => $product->id
            ])->first();

            if (empty($existProductLicenseKey))
            {
                $createLicenseData = [
                    'license_code' => 'TRIAL',
                    'entity_type' => 'PRODUCT',
                    'entity_ref_id' => $product->id,
                    'license_key' => config('app.trial_license_key')
                ];

                $createdLicense = LicenseKeyService::generateLicense($createLicenseData, true);
                $createdLicenseObj = json_decode($createdLicense);
                if(!empty($createdLicenseObj->data[0]->details)){
                    $existProductLicenseKey = $createdLicenseObj->data[0]->details;
                } else {
                    $existProductLicenseKey = $createdLicenseObj->data[0];
                }
            }
        }
        else
        {
            $existProductLicenseKey = ProductLicenseKeys::where([
                'license_key' => $data['license_key'],
                'product_id' => $product->id
            ])->first();
        }
        if (empty($existProductLicenseKey))
        {
            if ($data['license_key'] != config('app.trial_license_key'))
            {
                Log::debug("License Activation Failure: The given license key is not found, Input Data: ".json_encode($data));
                return json_encode(["status" => false, "code" => 422, "message" => "Data Not Found", "data" => ["error" => ["The given license key is not found"]], "status_code" => 422]);
            }
        }
        else if (@$existProductLicenseKey->status != 'AVAILABLE' && @$existProductLicenseKey->status != 'PURCHASED')
        {
            Log::debug("License Activation Failure: The given license key is already " . strtolower($existProductLicenseKey->status).", Input Data: ".json_encode($data));

            return json_encode(["status" => false, "code" => 422, "message" => "Request Denied", "data" => ["error" => ["The given license key is already " . strtolower($existProductLicenseKey->status)]], "status_code" => 422]);
        }
        else if ( $existProductLicenseKey->mac_address != '' && ($existProductLicenseKey->mac_address != $data['mac_address']) && ($data['license_key'] != config('app.trial_license_key')) )
        {
            Log::debug("License Activation Failure: The given license key is invalid,"." Input Data: ".json_encode($data));

            return json_encode(["status" => false, "code" => 422, "message" => "Request Denied", "data" => ["error" => ["The given license key is invalid"]], "status_code" => 422]);
        }

        // Check whether the customer already used the trial version
        if (@$existProductLicenseKey->license_type == 'TRIAL')
        {
            $checkDeviceAlreadyUsedTrial = ProductLicenseKeys::where([
                'product_id' => $product->id,
                'mac_address' => $data['mac_address'],
                'license_type' => $existProductLicenseKey->license_type,
            ])->where('id', '!=', $existProductLicenseKey->id)->first();

            if (!empty($checkDeviceAlreadyUsedTrial))
            {
                Log::debug("License Activation Failure: You have already used the trial version, Input Data: ".json_encode($data));

                return json_encode(["status" => false, "code" => 422, "message" => "Request Denied", "data" => ["error" => ["You have already used the trial version."]], "status_code" => 422]);
            }
        }

        $licenseProducts = LicenseProduct::find($existProductLicenseKey->license_type_id);

        if (!empty(@$data['email']))
        {
            $customerDetails = [
                'first_name' => (key_exists('first_name', $data)) ? $data['first_name'] : NULL,
                'last_name' => (key_exists('last_name', $data))? $data['last_name'] : NULL,
                'email' => (key_exists('email', $data))? $data['email'] : NULL,
                'phone_no' => (key_exists('phone_no', $data))? $data['phone_no'] : NULL,
            ];
            $customer = LicenseKeyService::saveCustomerDetails($customerDetails);
        }

        $productLicenseKeysData = [
            'mac_address' => (key_exists('mac_address', $data) && !empty($data['mac_address'])) ? $data['mac_address'] : $existProductLicenseKey->mac_address,
            //'updated_by' => auth()->user()->id,
        ];

        if ($existProductLicenseKey->status != 'PURCHASED')
        {
            $expiry_duration = str_replace(['(', ')'], ['', ''], $licenseProducts->expiry_duration);
            $todayDate = date('Y-m-d H:i:s');
            $expiryDate = date('Y-m-d H:i:s', strtotime($expiry_duration));

            if ( strtotime($expiry_duration) <= strtotime($todayDate) )
            {
                Log::debug("License Activation Failure: The given license key is already Expired, Input Data: ".json_encode($data));

                return json_encode(["status" => false, "code" => 422, "message" => "Request Denied", "data" => ["error" => ["The given license key is already Expired."]], "status_code" => 422]);
            }

            if (!empty($productLicenseKeysData['mac_address']))
            {
                $productLicenseKeysData['status'] = 'PURCHASED';
                $productLicenseKeysData['purchased_date'] = (strtotime($existProductLicenseKey->purchased_date) > 0) ? $existProductLicenseKey->purchased_date : $todayDate;
                // $productLicenseKeysData['expiry_date'] = (strtotime($existProductLicenseKey->expiry_date) > 0) ? $existProductLicenseKey->expiry_date : $expiryDate;
            }
        }

        if (!empty($customer))
        {
            $productLicenseKeysData['customer_id'] = $customer->id;
        }

        $productLicenseKeysModel = ProductLicenseKeys::updateRecord($productLicenseKeysData, $existProductLicenseKey->license_uuid);

        if ( (empty($existProductLicenseKey->mac_address) && !empty($productLicenseKeysModel->mac_address)) )
        {
            $system_info = [
                "BROWSER" => UserSystemInfoHelper::get_browsers(),
                "OS" => UserSystemInfoHelper::get_os(),
                "IP_ADDRESS" => UserSystemInfoHelper::get_ip(),
            ];
            $licenseAuditData = [
                'license_id' => $productLicenseKeysModel->id,
                'license_audit_uuid' => Uuid::generate(4),
                'entry_type' => 'MAC_ADDRESS_UPDATE',
                'license_key' => $productLicenseKeysModel->license_key,
                'mac_address' => $productLicenseKeysModel->mac_address,
                'system_info' => json_encode($system_info),
            ];

            LicenseAudit::insertRecord($licenseAuditData);
        }

        if ($productLicenseKeysModel)
        {
            $updatedData["license"] = [
                "license_key" => $productLicenseKeysModel->license_key,
                "license_type" => ucfirst(strtolower($productLicenseKeysModel->license_type)),
                "mac_address" => $productLicenseKeysModel->mac_address,
                "purchased_date" => $productLicenseKeysModel->purchased_date ,
                "expiry_date" => $productLicenseKeysModel->expiry_date,
                'status' => $productLicenseKeysModel->status,
            ];

            if (!empty($customer))
            {
                $updatedData["customer"] = [
                    'first_name' => $customer->first_name,
                    'last_name' => $customer->last_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ];
            }

            return json_encode(["status" => true, "code" => 200, "message" => "License Key Has Been Activated Successfully", "data" => $updatedData, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500]);
        }
    }

    public static function getLicenseProducts($data)
    {
        $result=[];
        if (!empty($data['license_key'])){
            $query = ProductLicenseKeys::where('license_key', $data['license_key'])->get();
            $query =DB::table('product_license_keys as plk')->join('products as p', 'plk.product_id', 'p.id')
            ->leftJoin('packages as pa', 'plk.package_id', 'pa.id')->whereNull('plk.deleted_at');
            if(!empty($data['mac_address'])){
                $query->where('plk.mac_address', $data['mac_address']);
            }
            if (@$data['license_key'] == config('app.trial_license_key')){
                $query->whereNotNull('plk.mac_address');
                if(!empty($data['mac_address'])){
                    $query->where('plk.mac_address', $data['mac_address']);
                } else {
                    $query->whereNull('plk.mac_address');
                }
            }
            $licence_data=$query->select('plk.*', 'p.product_name', 'pa.package_name', 'p.product_code', 'pa.package_code')->where('plk.license_key', $data['license_key'])->get();
            
            if(!empty($licence_data)){
                foreach ($licence_data as $key => $value) {
                    $temp_row=[];
                    $temp_row['license_id']=$value->license_uuid;
                    $temp_row['license_type']=$value->license_type;
                    $temp_row['mac_address']=$value->mac_address;
                    $temp_row['purchased_date']=$value->purchased_date;
                    $temp_row['expiry_date']=$value->expiry_date;
                    $temp_row['status']=$value->status;
                    $temp_row['product_name']=$value->product_name;
                    $temp_row['product_code']=$value->product_code;
                    $temp_row['package_name']=$value->package_name;
                    $temp_row['package_code']=$value->package_code;
                    $result[]=$temp_row;
                }
            }
        }
        return $result;
    }
    public static function getLicensesData($data, $user=null)
    {
        $query = new ProductLicenseKeys;

        if (!empty($data['license_key']))
            $query = $query->where('license_key', 'like', '%'. $data['license_key'] .'%');

        if (!empty($data['license_type']))
            $query = $query->where('license_type', 'like', '%'. $data['license_type'] .'%');

        if (!empty($data['mac_address']))
            $query = $query->where('mac_address', 'like', '%'. $data['mac_address'] .'%');

        if (!empty($data['status']))
        {
            if ($data['status'] != 'EXPIRED')
            {
                $query = $query->where( function($q) use ($data) {
                    $q->where('status', 'like', $data['status']);
                    $q->where(function($sub) {
                        $sub->where('expiry_date', '>', date('Y-m-d H:i:s'));
                        $sub->orWhereNull('expiry_date');
                    });
                });
            }

            else if ($data['status'] == 'EXPIRED')
            {
                $query = $query->where( function($q) use ($data) {
                    $q->where('status', 'like', $data['status']);
                    $q->orWhere(function($sub) {
                        $sub->where('expiry_date', '<=', date('Y-m-d H:i:s'));
                        $sub->whereNotNull('expiry_date');
                    });
                });
            }

        }
        else if (empty($data['status']))
        {
            $query = $query->where( function($q) use ($data) {
                $q->where('status', '!=', 'EXPIRED');
                $q->where(function($sub) {
                    $sub->where('expiry_date', '>', date('Y-m-d H:i:s'));
                    $sub->orWhereNull('expiry_date');
                });
            });
        }

        if (!empty($data['purchased_date_from']))
            $query = $query->whereDate('purchased_date', '>=', date('Y-m-d', strtotime($data['purchased_date_from'])));

        if (!empty($data['purchased_date_to']))
            $query = $query->whereDate('purchased_date', '<=', date('Y-m-d', strtotime($data['purchased_date_to'])));

        if (!empty($data['expiry_from_date']))
            $query = $query->whereDate('expiry_date', '>=', date('Y-m-d', strtotime($data['expiry_from_date'])));

        if (!empty($data['expiry_to_date']))
            $query = $query->whereDate('expiry_date', '<=', date('Y-m-d', strtotime($data['expiry_to_date'])));

        if (!empty($data['product_name']))
        {
            $query = $query->where(function($sub) use ($data) {
                $sub->whereHas('product', function($q) use ($data){
                    $q->where('product_name', 'like', '%'. $data['product_name']. '%');
                    $q->orWhere('product_id', 'like', '%'. $data['product_name']. '%');
                })->orWhereHas('package', function($q) use ($data){
                    $q->where('package_name', 'like', '%'. $data['product_name']. '%');
                });
            });
        }

        if (!empty($data['email']))
        {
            $query = $query->whereHas('customer', function($q) use ($data){
                $q->where('email', 'like', '%'. $data['email']. '%');
            });
        }

        if (!empty($data['order_reference_no']))
        {
            $query = $query->whereHas('order', function($q) use ($data){
                $q->where('order_reference_no', 'like', '%'. $data['order_reference_no']. '%');
            });
        }

        if (!empty($data['order_source']))
        {
            $query = $query->whereHas('order', function($q) use ($data){
                $q->where('order_source', 'like', '%'. $data['order_source']. '%');
            });
        }

        if (!empty($data['exclude_trial']) && $data['exclude_trial'] == 'true')
            $query = $query->where('license_type', '!=', 'TRIAL');

        if (!empty($data['search']))
        {
            $query = $query->where(function($sub) use ($data) {
                if ($data['search'] == 'Activated')
                {
                    $statusFilter = 'PURCHASED';
                }
                else
                {
                    $statusFilter = $data['search'];
                }

                $sub->where('license_key', 'like', '%'. $data['search'] .'%')
                ->orWhere('license_key', 'like', '%'. $data['search'] .'%')
                ->orWhere('license_type', 'like', '%'. $data['search'] .'%')
                ->orWhere('mac_address', 'like', '%'. $data['search'] .'%')
                ->orWhere('status', 'like', $statusFilter)
                ->orwhereHas('customer', function($q) use ($data){
                    $q->where('first_name', 'like', '%'. $data['search']. '%');
                    $q->orWhere('last_name', 'like', '%'. $data['search']. '%');
                    $q->orWhere('email', 'like', '%'. $data['search']. '%');
                    $q->orWhere('phone', 'like', '%'. $data['search']. '%');
                })->orwhereHas('order', function($q) use ($data){
                    $q->where('order_info', 'like', '%'. $data['search']. '%');
                    $q->orWhere('order_reference_no', 'like', '%'. $data['search']. '%');
                    $q->orWhere('order_source', 'like', '%'. $data['search']. '%');
                })->orwhereHas('product', function($q) use ($data){
                    $q->where('product_name', 'like', '%'. $data['search']. '%');
                    $q->orWhere('product_id', 'like', '%'. $data['search']. '%');
                })->orWhereHas('package', function($q) use ($data){
                    $q->where('package_name', 'like', '%'. $data['search']. '%');
                });
            });
        }

        $query = $query->orderBy((@$data['sort_by']) ? @$data['sort_by'] : 'created_at', (@$data['sort_order']) ? @$data['sort_order'] : 'DESC');

        if (!key_exists('page', $data) || $data['page'] == 'all')
            $licenses = $query->paginate(1000);
        else
            $licenses = $query->paginate(10);

        //dd(DB::getQueryLog());
        $hideColumns=['id'];
        if(!in_array(@$user->email, self::SHOW_LICENSE_KEY)){
            $hideColumns[]='license_key';
        }
        $licensesData = $licenses->map( function($license) use ($hideColumns) {
            if (!empty($license))
            {
                return $license->makeHidden($hideColumns);
            }
            else
            {
                return $license;
            }
        });

        $customers = $licenses->map( function($license) {
            if (!empty($license->customer))
            {
                return $license->customer->makeHidden(['id']);
            }
            else
            {
                return $license->customer;
            }
        });

        $orders = $licenses->map( function($license) {
            if (!empty($license->order))
            {
                return $license->order->makeHidden(['id']);
            }
            else
            {
                return $license->order;
            }
        });

        $products = $licenses->map( function($license) {
            if (!empty($license->product))
            {
                return $license->product->makeHidden(['id']);
            }
            else
            {
                return $license->product;
            }
        });

        $packages = $licenses->map( function($license) {
            if (!empty($license->package))
            {
                return $license->package->makeHidden(['id']);
            }
            else
            {
                return $license->package;
            }
        });

        $licenseProducts = $licenses->map( function($license) {
            if (!empty($license->licenseProduct))
            {
                return $license->licenseProduct->makeHidden(['id']);
            }
            else
            {
                return $license->licenseProduct;
            }
        });

        return json_encode(["status" => true, "code" => 200, "message" => "License Keys Data Retrieved Successfully", "data" => $licenses, "status_code" => 200]);
    }
    public static function get_license_list($data, $user=null, $is_system=false) {
        $query =DB::table('product_license_keys as plk')->join('products as p', 'plk.product_id', 'p.id')
        ->leftJoin('packages as pa', 'plk.package_id', 'pa.id')->whereNull('plk.deleted_at')
        ->leftJoin('orders as o', 'plk.order_id', 'o.id')->whereNull('o.deleted_at')
        ->leftJoin('customers as c', 'plk.customer_id', 'c.id')->whereNull('c.deleted_at');
        
        if(!empty($data['mac_address'])){
            $query->where('plk.mac_address', $data['mac_address']);
        }
        
        if(!empty($data['type']) && $data['type']=='PACKAGE'){
            // $query->whereIn('plk.id', function ($subQuery) use ($data) {
            //     $subQuery->from('product_license_keys as plk1')->select(DB::Raw('max(plk1.id) as max_id'))
            //         ->whereNull('plk1.deleted_at')
            //         ->groupBy('plk1.order_id', 'plk1.package_id', 'plk1.license_key');
            // });

            $query->whereIn('plk.id', function ($subQuery) use ($data) {
                $subQuery->select('id')
                    ->from(function ($subSubQuery) {
                        $subSubQuery->select('id', DB::Raw('dense_rank() over (PARTITION BY order_id, license_key ORDER BY purchased_date DESC, id ASC) as rank_no'))
                            ->from('product_license_keys')
                            ->whereNull('deleted_at');
                    }, 'sub')
                    ->where('rank_no', '=', 1);
            });
            $query->whereNotNull('pa.id');
        } else {
            $query->whereNull('pa.id');
        }
        if (!empty($data['license_key'])){
            $query->where('plk.license_key', 'like', '%'. $data['license_key']. '%');
        }
        if (!empty($data['license_type'])){
            $query->where('plk.license_type', 'like', '%'. $data['license_type']. '%');
        }
        if (!empty($data['product_name'])){
            $query->where('p.product_name', 'like', '%'. $data['product_name']. '%');
        }
        if (!empty($data['package_name'])){
            $query->where('pa.package_name', 'like', '%'. $data['package_name']. '%');
        }
        if (!empty($data['mac_address'])){
            $query->where('plk.mac_address', 'like', '%'. $data['mac_address']. '%');
        }
        if (!empty($data['email'])){
            $query->where('c.email', 'like', '%'. $data['email']. '%');
        }
        if (!empty($data['status']))
        {
            if ($data['status'] != 'EXPIRED')
            {
                $query->where( function($q) use ($data) {
                    $q->where('plk.status', 'like', $data['status']);
                    $q->where(function($sub) {
                        $sub->where('plk.expiry_date', '>', date('Y-m-d H:i:s'));
                        $sub->orWhereNull('plk.expiry_date');
                    });
                });
            }

            else if ($data['status'] == 'EXPIRED')
            {
                $query->where( function($q) use ($data) {
                    $q->where('plk.status', 'like', $data['status']);
                    $q->where(function($sub) {
                        $sub->where('plk.expiry_date', '<=', date('Y-m-d H:i:s'));
                        $sub->whereNotNull('plk.expiry_date');
                    });
                });
            }

        }
        else if (empty($data['status']))
        {
            $query->where( function($q) use ($data) {
                $q->where('plk.status', '!=', 'EXPIRED');
                $q->where(function($sub) {
                    $sub->where('plk.expiry_date', '>', date('Y-m-d H:i:s'));
                    $sub->orWhereNull('plk.expiry_date');
                });
            });
        }
        if (!empty($data['search'])){
            $query->where(function($sub) use ($data) {
                if ($data['search'] == 'Activated') {
                    $statusFilter = 'PURCHASED';
                } else {
                    $statusFilter = $data['search'];
                }
                $sub->where('pa.package_name', 'like', '%'. $data['search']. '%');
                $sub->orWhere('p.product_name', 'like', '%'. $data['search']. '%');
                $sub->orWhere('plk.status', 'like', '%'. $data['search']. '%');
                $sub->orWhere('c.email', 'like', '%'. $data['search']. '%');
                $sub->orWhere('c.first_name', 'like', '%'. $data['search']. '%');
                $sub->orWhere('c.phone', 'like', '%'. $data['search']. '%');
                $sub->orWhere('plk.license_key', 'like', '%'. $data['search']. '%');
                $sub->orWhere('plk.license_type', 'like', '%'. $data['search']. '%');
                $sub->orWhere('plk.mac_address', 'like', '%'. $data['search']. '%');
            });
        }

        if (!empty($data['expiry_from_date'])) {
            $convert_date = date("Y-m-d 00:00:00", strtotime($data['expiry_from_date']));
            $date = AppHelper::convertTimezone($convert_date, @$user->timezone, "UTC");
            $query->where('plk.expiry_date', '>=', $date);
        }
        if (!empty($data['expiry_to_date'])) {
            $convert_date = date("Y-m-d 23:59:59", strtotime($data['expiry_to_date']));
            $date = AppHelper::convertTimezone($convert_date, @$user->timezone, "UTC");
            $query->where('plk.expiry_date', '<=', $date);
        }

        if (!empty($data['purchased_from_date'])) {
            $convert_date = date("Y-m-d 00:00:00", strtotime($data['purchased_from_date']));
            $date = AppHelper::convertTimezone($convert_date, @$user->timezone, "UTC");
            $query->where('o.order_placed_at', '>=', $date);
        }
        if (!empty($data['purchased_to_date'])) {
            $convert_date = date("Y-m-d 23:59:59", strtotime($data['purchased_to_date']));
            $date = AppHelper::convertTimezone($convert_date, @$user->timezone, "UTC");
            $query->where('o.order_placed_at', '<=', $date);
        }

        
        $query->select('plk.*', 
            'p.product_uuid', 'p.product_name', 'p.product_code', 'p.product_prefix', 'p.product_number', 'p.product_id as p_product_id'
            , 'p.description', 'p.package_content', 'p.status as product_status', 'p.description as p_description'
            , 'pa.package_uuid', 'pa.package_name', 'pa.package_code', 'pa.exclusive_package', 'pa.product_codes', 'pa.status as pa_status'
            , 'c.customer_uuid', 'c.wp_customer_id', 'c.user_name', 'c.first_name', 'c.last_name', 'c.email', 'c.phone', 'c.status as c_status'
            , 'o.order_uuid', 'o.wp_order_id', 'o.order_source', 'o.order_reference_no', 'o.order_type', 'o.order_status', 'o.payment_status', 'o.total_price', 'o.source', 'o.order_placed_at'
        );
        $page_no=(!empty($data['page_no']) && $data['page_no']>0)?$data['page_no']:1;
        $limit=(!empty($data['limit']) && $data['limit']<1000)?$data['limit']:100;
        $offset=($page_no * $limit)-$limit;
        // dd($query->toSql());
        $total_count=$query->count();
        $query->limit($limit)->offset($offset);
        $licence_data=$query->orderBy('plk.updated_at','DESC')->get();
        $result=[];
        if(!empty($licence_data)){
            foreach ($licence_data as $key => $value) {
                
                $temp_row=[];
                $temp_row['license_type_id']=$value->license_type_id;
                $temp_row['product_id']=$value->product_id;
                $temp_row['package_id']=$value->package_id;
                $temp_row['license_uuid']=$value->license_uuid;
                $temp_row['license_type']=$value->license_type;
                if(in_array(@$user->email, self::SHOW_LICENSE_KEY) || !empty($is_system)){
                    $temp_row['license_key']=$value->license_key;
                }
                
                $temp_row['mac_address']=$value->mac_address;
                $temp_row['license_info']=$value->license_info;
                $temp_row['expiry_date']=$value->expiry_date;
                $temp_row['purchased_date']=$value->purchased_date;
                $temp_row['status']=$value->status;
                $temp_row['wp_order_item_id']=$value->wp_order_item_id;
                $temp_row['customer_id']=$value->customer_id;
                $temp_row['order_id']=$value->order_id;
                $temp_row['created_by']=$value->created_by;
                $temp_row['updated_by']=$value->updated_by;
                $temp_row['created_at']=$value->created_at;
                $temp_row['updated_at']=$value->updated_at;
                $temp_row['deleted_at']=$value->deleted_at;
                // $temp_row['hashed_license_key']=$value->license_key;
                $temp_row['hashed_license_key']=LicenseKeyHelper::licenseKeyHash($value->license_key);
                
                $temp_row['customer']=[];
                $temp_row['customer']['customer_uuid']=$value->customer_uuid;
                $temp_row['customer']['wp_customer_id']=$value->wp_customer_id;
                $temp_row['customer']['user_name']=$value->user_name;
                $temp_row['customer']['first_name']=$value->first_name;
                $temp_row['customer']['last_name']=$value->last_name;
                $temp_row['customer']['email']=$value->email;
                $temp_row['customer']['phone']=$value->phone;
                $temp_row['customer']['status']=$value->c_status;

                $temp_row['license_product']=[];
                $temp_row['product']=[];
                $temp_row['product']['product_uuid']=$value->product_uuid;
                $temp_row['product']['product_name']=$value->product_name;
                $temp_row['product']['product_prefix']=$value->product_prefix;
                $temp_row['product']['product_code']=$value->product_code;
                $temp_row['product']['product_id']=$value->p_product_id;
                $temp_row['product']['description']=$value->p_description;
                $temp_row['product']['status']=$value->product_status;
                $temp_row['package']=[];
                $temp_row['package']['package_uuid']=$value->package_uuid;
                $temp_row['package']['package_name']=$value->package_name;
                $temp_row['package']['package_code']=$value->package_code;
                $temp_row['package']['exclusive_package']=$value->exclusive_package;
                $temp_row['package']['product_codes']=$value->product_codes;
                $temp_row['package']['status']=$value->pa_status;

                $temp_row['order']=[];
                $temp_row['order']['order_uuid']=$value->order_uuid;
                $temp_row['order']['wp_order_id']=$value->wp_order_id;
                $temp_row['order']['order_source']=$value->order_source;
                $temp_row['order']['order_reference_no']=$value->order_reference_no;
                $temp_row['order']['order_type']=$value->order_type;
                $temp_row['order']['order_status']=$value->order_status;
                $temp_row['order']['payment_status']=$value->payment_status;
                $temp_row['order']['total_price']=$value->total_price;
                $temp_row['order']['source']=$value->source;
                $temp_row['order']['order_placed_at']=$value->order_placed_at;
                $result[]=$temp_row;
            }
        }
        $response=[];
        $response['licenses']=$result;
        $response['total_count']=$total_count;
        return $response;
    }
    public static function listLicensesData($data, $user=null)
    {

        $query = new ProductLicenseKeys;

        if (!empty($data['license_key'])){
            $query = $query->where('license_key', 'like', '%'. $data['license_key'] .'%');
        }

        if (!empty($data['license_type'])){
            $query = $query->where('license_type', 'like', '%'. $data['license_type'] .'%');
        }

        if (!empty($data['mac_address'])){
            $query = $query->where('mac_address', 'like', '%'. $data['mac_address'] .'%');
        }
        if (!empty($data['type']) && $data['type']=='PRODUCT'){
            $query = $query->whereNull('package_id');
        } else if (!empty($data['type']) && $data['type']=='PACKAGE'){
            $query = $query->whereNotNull('package_id');
        }
        
        if (!empty($data['status']))
        {
            if ($data['status'] != 'EXPIRED')
            {
                $query = $query->where( function($q) use ($data) {
                    $q->where('status', 'like', $data['status']);
                    $q->where(function($sub) {
                        $sub->where('expiry_date', '>', date('Y-m-d H:i:s'));
                        $sub->orWhereNull('expiry_date');
                    });
                });
            }

            else if ($data['status'] == 'EXPIRED')
            {
                $query = $query->where( function($q) use ($data) {
                    $q->where('status', 'like', $data['status']);
                    $q->orWhere(function($sub) {
                        $sub->where('expiry_date', '<=', date('Y-m-d H:i:s'));
                        $sub->whereNotNull('expiry_date');
                    });
                });
            }

        }
        else if (empty($data['status']))
        {
            $query = $query->where( function($q) use ($data) {
                $q->where('status', '!=', 'EXPIRED');
                $q->where(function($sub) {
                    $sub->where('expiry_date', '>', date('Y-m-d H:i:s'));
                    $sub->orWhereNull('expiry_date');
                });
            });
        }
        if (!empty($data['purchased_date_from']))
            $query = $query->whereDate('purchased_date', '>=', date('Y-m-d', strtotime($data['purchased_date_from'])));

        if (!empty($data['purchased_date_to']))
            $query = $query->whereDate('purchased_date', '<=', date('Y-m-d', strtotime($data['purchased_date_to'])));

        if (!empty($data['expiry_from_date']))
            $query = $query->whereDate('expiry_date', '>=', date('Y-m-d', strtotime($data['expiry_from_date'])));

        if (!empty($data['expiry_to_date']))
            $query = $query->whereDate('expiry_date', '<=', date('Y-m-d', strtotime($data['expiry_to_date'])));

        if (!empty($data['product_name']))
        {
            $query = $query->where(function($sub) use ($data) {
                $sub->whereHas('product', function($q) use ($data){
                    $q->where('product_name', 'like', '%'. $data['product_name']. '%');
                    $q->orWhere('product_id', 'like', '%'. $data['product_name']. '%');
                })->orWhereHas('package', function($q) use ($data){
                    $q->where('package_name', 'like', '%'. $data['product_name']. '%');
                });
            });
        }

        if (!empty($data['email']))
        {
            $query = $query->whereHas('customer', function($q) use ($data){
                $q->where('email', 'like', '%'. $data['email']. '%');
            });
        }

        if (!empty($data['order_reference_no']))
        {
            $query = $query->whereHas('order', function($q) use ($data){
                $q->where('order_reference_no', 'like', '%'. $data['order_reference_no']. '%');
            });
        }

        if (!empty($data['order_source']))
        {
            $query = $query->whereHas('order', function($q) use ($data){
                $q->where('order_source', 'like', '%'. $data['order_source']. '%');
            });
        }

        if (!empty($data['exclude_trial']) && $data['exclude_trial'] == 'true')
            $query = $query->where('license_type', '!=', 'TRIAL');

        if (!empty($data['search']))
        {
            $query = $query->where(function($sub) use ($data) {
                if ($data['search'] == 'Activated') {
                    $statusFilter = 'PURCHASED';
                } else {
                    $statusFilter = $data['search'];
                }

                $sub->where('license_key', 'like', '%'. $data['search'] .'%')
                ->orWhere('license_key', 'like', '%'. $data['search'] .'%')
                ->orWhere('license_type', 'like', '%'. $data['search'] .'%')
                ->orWhere('mac_address', 'like', '%'. $data['search'] .'%')
                ->orWhere('status', 'like', $statusFilter)
                ->orwhereHas('customer', function($q) use ($data){
                    $q->where('first_name', 'like', '%'. $data['search']. '%');
                    $q->orWhere('last_name', 'like', '%'. $data['search']. '%');
                    $q->orWhere('email', 'like', '%'. $data['search']. '%');
                    $q->orWhere('phone', 'like', '%'. $data['search']. '%');
                })->orwhereHas('order', function($q) use ($data){
                    $q->where('order_info', 'like', '%'. $data['search']. '%');
                    $q->orWhere('order_reference_no', 'like', '%'. $data['search']. '%');
                    $q->orWhere('order_source', 'like', '%'. $data['search']. '%');
                })->orwhereHas('product', function($q) use ($data){
                    $q->where('product_name', 'like', '%'. $data['search']. '%');
                    $q->orWhere('product_id', 'like', '%'. $data['search']. '%');
                })->orWhereHas('package', function($q) use ($data){
                    $q->where('package_name', 'like', '%'. $data['search']. '%');
                });
            });
        }

        $query = $query->orderBy((@$data['sort_by']) ? @$data['sort_by'] : 'created_at', (@$data['sort_order']) ? @$data['sort_order'] : 'DESC');

        if (!key_exists('page', $data) || $data['page'] == 'all'){
            $licenses = $query->paginate(1000);
        } else {
            $licenses = $query->paginate(10);
        }

        // dd($data, $query->toSql());
        //dd(DB::getQueryLog());
        $hidden_fields=['id'];
        if(!in_array(@$user->email, self::SHOW_LICENSE_KEY)){
            $hidden_fields[]='license_key';
        }
        $licensesData = $licenses->map( function($license) use ($hidden_fields) {
            if (!empty($license)) {
                return $license->makeHidden($hidden_fields);
            } else {
                return $license;
            }
        });

        $customers = $licenses->map( function($license) {
            if (!empty($license->customer)) {
                return $license->customer->makeHidden(['id']);
            } else {
                return $license->customer;
            }
        });

        $orders = $licenses->map( function($license) {
            if (!empty($license->order)) {
                return $license->order->makeHidden(['id']);
            } else {
                return $license->order;
            }
        });

        $products = $licenses->map( function($license) {
            if (!empty($license->product)) {
                return $license->product->makeHidden(['id']);
            } else {
                return $license->product;
            }
        });

        $packages = $licenses->map( function($license) {
            if (!empty($license->package)) {
                return $license->package->makeHidden(['id']);
            } else {
                return $license->package;
            }
        });

        $licenseProducts = $licenses->map( function($license) {
            if (!empty($license->licenseProduct)) {
                return $license->licenseProduct->makeHidden(['id']);
            } else {
                return $license->licenseProduct;
            }
        });

        return json_encode(["status" => true, "code" => 200, "message" => "License Keys Data Retrieved Successfully", "data" => $licenses, "status_code" => 200]);
    }

    // public static function listLicensesData($data)
    // {

    //     $query = new ProductLicenseKeys;

    //     if (!empty($data['license_key'])){
    //         $query = $query->where('license_key', 'like', '%'. $data['license_key'] .'%');
    //     }

    //     if (!empty($data['license_type'])){
    //         $query = $query->where('license_type', 'like', '%'. $data['license_type'] .'%');
    //     }

    //     if (!empty($data['mac_address'])){
    //         $query = $query->where('mac_address', 'like', '%'. $data['mac_address'] .'%');
    //     }
    //     if (!empty($data['type']) && $data['type']=='PRODUCT'){
    //         $query = $query->whereNull('package_id');
    //     } else if (!empty($data['type']) && $data['type']=='PACKAGE'){
    //         $query = $query->whereNotNull('package_id');
    //     }
        
    //     if (!empty($data['status']))
    //     {
    //         if ($data['status'] != 'EXPIRED')
    //         {
    //             $query = $query->where( function($q) use ($data) {
    //                 $q->where('status', 'like', $data['status']);
    //                 $q->where(function($sub) {
    //                     $sub->where('expiry_date', '>', date('Y-m-d H:i:s'));
    //                     $sub->orWhereNull('expiry_date');
    //                 });
    //             });
    //         }

    //         else if ($data['status'] == 'EXPIRED')
    //         {
    //             $query = $query->where( function($q) use ($data) {
    //                 $q->where('status', 'like', $data['status']);
    //                 $q->orWhere(function($sub) {
    //                     $sub->where('expiry_date', '<=', date('Y-m-d H:i:s'));
    //                     $sub->whereNotNull('expiry_date');
    //                 });
    //             });
    //         }

    //     }
    //     else if (empty($data['status']))
    //     {
    //         $query = $query->where( function($q) use ($data) {
    //             $q->where('status', '!=', 'EXPIRED');
    //             $q->where(function($sub) {
    //                 $sub->where('expiry_date', '>', date('Y-m-d H:i:s'));
    //                 $sub->orWhereNull('expiry_date');
    //             });
    //         });
    //     }

    //     if (!empty($data['purchased_date_from']))
    //         $query = $query->whereDate('purchased_date', '>=', date('Y-m-d', strtotime($data['purchased_date_from'])));

    //     if (!empty($data['purchased_date_to']))
    //         $query = $query->whereDate('purchased_date', '<=', date('Y-m-d', strtotime($data['purchased_date_to'])));

    //     if (!empty($data['expiry_from_date']))
    //         $query = $query->whereDate('expiry_date', '>=', date('Y-m-d', strtotime($data['expiry_from_date'])));

    //     if (!empty($data['expiry_to_date']))
    //         $query = $query->whereDate('expiry_date', '<=', date('Y-m-d', strtotime($data['expiry_to_date'])));

    //     if (!empty($data['product_name']))
    //     {
    //         $query = $query->where(function($sub) use ($data) {
    //             $sub->whereHas('product', function($q) use ($data){
    //                 $q->where('product_name', 'like', '%'. $data['product_name']. '%');
    //                 $q->orWhere('product_id', 'like', '%'. $data['product_name']. '%');
    //             })->orWhereHas('package', function($q) use ($data){
    //                 $q->where('package_name', 'like', '%'. $data['product_name']. '%');
    //             });
    //         });
    //     }

    //     if (!empty($data['email']))
    //     {
    //         $query = $query->whereHas('customer', function($q) use ($data){
    //             $q->where('email', 'like', '%'. $data['email']. '%');
    //         });
    //     }

    //     if (!empty($data['order_reference_no']))
    //     {
    //         $query = $query->whereHas('order', function($q) use ($data){
    //             $q->where('order_reference_no', 'like', '%'. $data['order_reference_no']. '%');
    //         });
    //     }

    //     if (!empty($data['order_source']))
    //     {
    //         $query = $query->whereHas('order', function($q) use ($data){
    //             $q->where('order_source', 'like', '%'. $data['order_source']. '%');
    //         });
    //     }

    //     if (!empty($data['exclude_trial']) && $data['exclude_trial'] == 'true')
    //         $query = $query->where('license_type', '!=', 'TRIAL');

    //     if (!empty($data['search']))
    //     {
    //         $query = $query->where(function($sub) use ($data) {
    //             if ($data['search'] == 'Activated') {
    //                 $statusFilter = 'PURCHASED';
    //             } else {
    //                 $statusFilter = $data['search'];
    //             }

    //             $sub->where('license_key', 'like', '%'. $data['search'] .'%')
    //             ->orWhere('license_key', 'like', '%'. $data['search'] .'%')
    //             ->orWhere('license_type', 'like', '%'. $data['search'] .'%')
    //             ->orWhere('mac_address', 'like', '%'. $data['search'] .'%')
    //             ->orWhere('status', 'like', $statusFilter)
    //             ->orwhereHas('customer', function($q) use ($data){
    //                 $q->where('first_name', 'like', '%'. $data['search']. '%');
    //                 $q->orWhere('last_name', 'like', '%'. $data['search']. '%');
    //                 $q->orWhere('email', 'like', '%'. $data['search']. '%');
    //                 $q->orWhere('phone', 'like', '%'. $data['search']. '%');
    //             })->orwhereHas('order', function($q) use ($data){
    //                 $q->where('order_info', 'like', '%'. $data['search']. '%');
    //                 $q->orWhere('order_reference_no', 'like', '%'. $data['search']. '%');
    //                 $q->orWhere('order_source', 'like', '%'. $data['search']. '%');
    //             })->orwhereHas('product', function($q) use ($data){
    //                 $q->where('product_name', 'like', '%'. $data['search']. '%');
    //                 $q->orWhere('product_id', 'like', '%'. $data['search']. '%');
    //             })->orWhereHas('package', function($q) use ($data){
    //                 $q->where('package_name', 'like', '%'. $data['search']. '%');
    //             });
    //         });
    //     }

    //     $query = $query->orderBy((@$data['sort_by']) ? @$data['sort_by'] : 'created_at', (@$data['sort_order']) ? @$data['sort_order'] : 'DESC');

    //     if (!key_exists('page', $data) || $data['page'] == 'all'){
    //         $licenses = $query->paginate(1000);
    //     } else {
    //         $licenses = $query->paginate(10);
    //     }
    //     // dd($data, $query->toSql());
    //     //dd(DB::getQueryLog());

    //     $licensesData = $licenses->map( function($license) {
    //         if (!empty($license))
    //         {
    //             return $license->makeHidden(['license_key', 'id']);
    //         }
    //         else
    //         {
    //             return $license;
    //         }
    //     });

    //     $customers = $licenses->map( function($license) {
    //         if (!empty($license->customer))
    //         {
    //             return $license->customer->makeHidden(['id']);
    //         }
    //         else
    //         {
    //             return $license->customer;
    //         }
    //     });

    //     $orders = $licenses->map( function($license) {
    //         if (!empty($license->order))
    //         {
    //             return $license->order->makeHidden(['id']);
    //         }
    //         else
    //         {
    //             return $license->order;
    //         }
    //     });

    //     $products = $licenses->map( function($license) {
    //         if (!empty($license->product))
    //         {
    //             return $license->product->makeHidden(['id']);
    //         }
    //         else
    //         {
    //             return $license->product;
    //         }
    //     });

    //     $packages = $licenses->map( function($license) {
    //         if (!empty($license->package))
    //         {
    //             return $license->package->makeHidden(['id']);
    //         }
    //         else
    //         {
    //             return $license->package;
    //         }
    //     });

    //     $licenseProducts = $licenses->map( function($license) {
    //         if (!empty($license->licenseProduct))
    //         {
    //             return $license->licenseProduct->makeHidden(['id']);
    //         }
    //         else
    //         {
    //             return $license->licenseProduct;
    //         }
    //     });

    //     return json_encode(["status" => true, "code" => 200, "message" => "License Keys Data Retrieved Successfully", "data" => $licenses, "status_code" => 200]);
    // }


    public static function getActualLicenseData($data, $user)
    {
        $productLicenseKey = ProductLicenseKeys::where('license_uuid', $data['id'])->first();
        if (!empty($productLicenseKey))
        {
            $licenseKey = ['license_key' => $productLicenseKey->license_key];
            return json_encode(["status" => true, "code" => 200, "message" => "License Key Retrieved Successfully", "data" => $licenseKey, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => "", "status_code" => 422]);
        }
    }


    public static function saveCustomerDetails($data)
    {
        $existCustomer = Customer::where('email', $data['email'])->whereNotNull('email')->first();

        $phone = (!empty(@$data['phone_no'])) ? @$data['phone_no']: @$data['phone'];

        if(empty($existCustomer) && !empty($phone)){
            $existCustomer = Customer::where('phone', $phone)->whereNotNull('phone')->first();
        }

        if (empty($existCustomer))
        {
            $customerData = [
                'customer_uuid' => Uuid::generate(4),
                'user_name' => '',
                'first_name' => (key_exists('first_name', $data)) ? $data['first_name'] : '',
                'last_name' => (key_exists('last_name', $data))? $data['last_name'] : '',
                'email' => (key_exists('email', $data))? $data['email'] : '',
                'phone' => $phone,
                'status' => 'ACTIVE',
                //'created_by' => auth()->user()->id,
            ];

            $customer = Customer::insertRecord($customerData);
        }
        else
        {
            $customerData = [
                'first_name' => ( key_exists('first_name', $data) && !empty($data['first_name']) ) ? $data['first_name'] : $existCustomer->first_name,
                'last_name' => (key_exists('last_name', $data) && !empty($data['last_name']) ) ? $data['last_name'] : $existCustomer->last_name,
                'phone' => (!empty($phone) ) ? $phone : $existCustomer->phone,
                //'updated_by' => auth()->user()->id,
            ];

            $customer = Customer::updateRecord($customerData, $existCustomer->id);
        }
        return $customer;
    }


    public static function resetMacAddressProcess($data)
    {
        $existProductLicenseKey = ProductLicenseKeys::where('license_uuid', $data['id'])->first();

        if (!empty($existProductLicenseKey))
        {
            $updateData = [
                'mac_address' => '',
                'updated_by' => auth()->user()->id,
            ];

            if ($existProductLicenseKey->status == 'PURCHASED')
            {
                $updateData['status'] = 'AVAILABLE';
            }

            $productLicenseKey = ProductLicenseKeys::updateRecord($updateData, $data['id']);

            if ($productLicenseKey)
            {
                $productLicenseKey = LicenseKeyService::getLicenseRelationalData($productLicenseKey);
                if ( !empty($existProductLicenseKey->mac_address) && empty($productLicenseKey->mac_address) )
                {
                    $licenseAuditData = [
                        'license_id' => $productLicenseKey->id,
                        'license_audit_uuid' => Uuid::generate(4),
                        'entry_type' => 'MAC_ADDRESS_RESET',
                        'license_key' => $productLicenseKey->license_key,
                        'mac_address' => $existProductLicenseKey->mac_address,
                        'user_id' => auth()->user()->id
                    ];

                    LicenseAudit::insertRecord($licenseAuditData);
                }

                return json_encode(["status" => true, "code" => 200, "message" => "MAC Address Has Been Reset Successfully", "data" => $productLicenseKey, "status_code" => 200]);
            }
            else
            {
                return json_encode(["status" => false, "code" => 422, "message" => "Request Denied", "data" => ["error" => ["MAC Address Not Found"]], "status_code" => 422]);
            }
        }
    }


    public static function getProductBasedLicenseCountData($data)
    {
        $query = new ProductLicenseKeys;

        if (!empty($data['status']))
        {
           $query = $query->where('status', $data['status']);
        }

        $query = $query->selectRaw("id, COUNT(license_key) AS license_count, product_id")->groupBy('product_id');
        $licenses = $query->get();
        //dd(DB::getQueryLog());

        $product = $licenses->map( function($license) {
            return $license->product;
        });

        $productBasedCount = [];
        foreach ($licenses as $license)
        {
            if (isset($license->product))
            {
                $productBasedCount[$license->product->product_code] = $license->license_count;
            }
        }

        return json_encode(["status" => true, "code" => 200, "message" => "License Keys Count Retrieved Successfully", "data" => $productBasedCount, "status_code" => 200]);
    }


    public static function licenseDeactivateProcess($data, $user=null)
    {
        $deactivatedLicenses = [];
        $existProductLicenseKey = ProductLicenseKeys::where('license_uuid', $data['id'])->first();
        $hideLicenseKey=true;
        if(in_array(@$user->email, self::SHOW_LICENSE_KEY)){
            $hideLicenseKey=false;
        }
        if ($data['deactivate_type'] == 'PRODUCT_CODE')
        {
            $updateData = ['status' => 'DEACTIVATED', 'updated_by' => auth()->user()->id];
            $licenseKey = ProductLicenseKeys::updateRecord($updateData, $data['id']);
            $licenseKey = LicenseKeyService::getLicenseRelationalData($licenseKey, $hideLicenseKey);
            $deactivatedLicenses[] = $licenseKey;

            $licenseAuditData = [
                'license_id' => $licenseKey->id,
                'license_audit_uuid' => Uuid::generate(4),
                'entry_type' => 'LICENSE_DELETE',
                'license_key' => $licenseKey->license_key,
                'mac_address' => $licenseKey->mac_address,
                'user_id' => auth()->user()->id
            ];

            LicenseAudit::insertRecord($licenseAuditData);
        }

        else if ($data['deactivate_type'] == 'PACKAGE')
        {
            $productLicenseKeys = ProductLicenseKeys::where([
                'license_key' => $existProductLicenseKey->license_key,
                'package_id' => $existProductLicenseKey->package_id
            ])->get();

            foreach ($productLicenseKeys as $productLicenseKey)
            {
                $updateData = ['status' => 'DEACTIVATED', 'updated_by' => auth()->user()->id];
                $licenseKey = ProductLicenseKeys::updateRecord($updateData, $productLicenseKey->license_uuid);
                $licenseKey = LicenseKeyService::getLicenseRelationalData($licenseKey, $hideLicenseKey);
                $deactivatedLicenses[] = $licenseKey;

                $licenseAuditData = [
                    'license_id' => $licenseKey->id,
                    'license_audit_uuid' => Uuid::generate(4),
                    'entry_type' => 'LICENSE_DELETE',
                    'license_key' => $licenseKey->license_key,
                    'mac_address' => $licenseKey->mac_address,
                    'user_id' => auth()->user()->id
                ];

                LicenseAudit::insertRecord($licenseAuditData);
            }
        }

        if (!empty($deactivatedLicenses))
        {
            return json_encode(["status" => true, "code" => 200, "message" => "License Key Has Been Deactivated Successfully", "data" => $deactivatedLicenses, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => "", "status_code" => 422]);
        }
    }


    public static function licenseActivateProcess($data, $user)
    {
        $activatedLicenses = [];
        $existProductLicenseKey = ProductLicenseKeys::where('license_uuid', $data['id'])->first();
        $hideLicenseKey=true;
        if(in_array($user->email, self::SHOW_LICENSE_KEY)){
            $hideLicenseKey=false;
        }
        if ($data['activate_type'] == 'PRODUCT_CODE')
        {
            if ($existProductLicenseKey->expiry_date > date('Y-m-d H:i:s') && strtotime($existProductLicenseKey->purchased_date) > 0)
                $updateData = ['status' => 'PURCHASED'];
            else if ($existProductLicenseKey->expiry_date <= date('Y-m-d H:i:s') && strtotime($existProductLicenseKey->purchased_date) > 0)
                $updateData = ['status' => 'EXPIRED'];
            else if (strtotime($existProductLicenseKey->expiry_date) <= 0 && strtotime($existProductLicenseKey->purchased_date) <= 0)
                $updateData = ['status' => 'AVAILABLE'];

            $updateData['updated_by'] = auth()->user()->id;

            $licenseKey = ProductLicenseKeys::updateRecord($updateData, $data['id']);
            $licenseKey = LicenseKeyService::getLicenseRelationalData($licenseKey, $hideLicenseKey);
            $activatedLicenses[] = $licenseKey;

            $licenseAuditData = [
                'license_id' => $licenseKey->id,
                'license_audit_uuid' => Uuid::generate(4),
                'entry_type' => 'LICENSE_DELETE',
                'license_key' => $licenseKey->license_key,
                'mac_address' => $licenseKey->mac_address,
                'user_id' => auth()->user()->id
            ];

            LicenseAudit::insertRecord($licenseAuditData);
        }

        else if ($data['activate_type'] == 'PACKAGE')
        {
            $productLicenseKeys = ProductLicenseKeys::where([
                'license_key' => $existProductLicenseKey->license_key,
                'package_id' => $existProductLicenseKey->package_id
            ])->get();

            foreach ($productLicenseKeys as $productLicenseKey)
            {
                if ($productLicenseKey->expiry_date > date('Y-m-d H:i:s') && strtotime($productLicenseKey->purchased_date) > 0)
                    $updateData = ['status' => 'PURCHASED'];
                else if ($productLicenseKey->expiry_date <= date('Y-m-d H:i:s') && strtotime($productLicenseKey->purchased_date) > 0)
                    $updateData = ['status' => 'EXPIRED'];
                else if (strtotime($productLicenseKey->expiry_date) <= 0 && strtotime($productLicenseKey->purchased_date) <= 0)
                    $updateData = ['status' => 'AVAILABLE'];

                $updateData['updated_by'] = auth()->user()->id;

                $licenseKey = ProductLicenseKeys::updateRecord($updateData, $productLicenseKey->license_uuid);
                $licenseKey = LicenseKeyService::getLicenseRelationalData($licenseKey, $hideLicenseKey);
                $activatedLicenses[] = $licenseKey;

                $licenseAuditData = [
                    'license_id' => $licenseKey->id,
                    'license_audit_uuid' => Uuid::generate(4),
                    'entry_type' => 'LICENSE_DELETE',
                    'license_key' => $licenseKey->license_key,
                    'mac_address' => $licenseKey->mac_address,
                    'user_id' => auth()->user()->id
                ];

                LicenseAudit::insertRecord($licenseAuditData);
            }
        }

        if (!empty($activatedLicenses))
        {
            return json_encode(["status" => true, "code" => 200, "message" => "License Key Has Been Activated Successfully", "data" => $activatedLicenses, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => "", "status_code" => 422]);
        }
    }

    public static function licenseResetProcessV2($data, $user)
    {
        $licenses = [];
        $existProductLicenseKey = ProductLicenseKeys::where('license_uuid', $data['id'])->first();
        if(!empty($existProductLicenseKey)){
            if ($data['type'] == 'PRODUCT')
            {
                $productLicenseKeys = ProductLicenseKeys::where('license_uuid', $data['id'])->get();
            } else if ($data['type'] == 'PACKAGE'){
                $productLicenseKeys = ProductLicenseKeys::where([
                    'license_key' => $existProductLicenseKey->license_key,
                    'package_id' => $existProductLicenseKey->package_id
                ])->get();
            }
            if(!empty($productLicenseKeys) && count($productLicenseKeys)>0){
                foreach ($productLicenseKeys as $key => $value) {
                    $updateData = [
                        'mac_address' => '',
                        'updated_by' => $user->id,
                    ];
        
                    if ($existProductLicenseKey->status == 'PURCHASED')
                    {
                        $updateData['status'] = 'AVAILABLE';
                    }

                    $productLicenseKey = ProductLicenseKeys::updateRecord($updateData, $value->license_uuid);
                    if ($productLicenseKey){
                        $productLicenseKey = LicenseKeyService::getLicenseRelationalData($productLicenseKey);
                        if ( !empty($value->mac_address) && empty($productLicenseKey->mac_address) )
                        {
                            $licenseAuditData = [
                                'license_id' => $productLicenseKey->id,
                                'license_audit_uuid' => Uuid::generate(4),
                                'entry_type' => 'MAC_ADDRESS_RESET',
                                'license_key' => $productLicenseKey->license_key,
                                'mac_address' => $value->mac_address,
                                'user_id' => auth()->user()->id
                            ];

                            LicenseAudit::insertRecord($licenseAuditData);
                        }
                    }
                    $licenses[]=$productLicenseKey;
                }
            }
        }
        return json_encode(["status" => true, "code" => 200, "message" => "MAC Address Has Been Reset Successfully", "data" => $licenses, "status_code" => 200]);
    }


    public static function deleteLicensekeyProcess($data)
    {
        $deletedLicenses = [];
        $existProductLicenseKey = ProductLicenseKeys::where('license_uuid', $data['id'])->first();

        if ($data['delete_type'] == 'PRODUCT_CODE')
        {
            $updateData = ['updated_by' => auth()->user()->id];
            $licenseKey = ProductLicenseKeys::updateAndDeleteRecord($updateData, $data['id']);
            $licenseKey = LicenseKeyService::getLicenseRelationalData($licenseKey);
            $deletedLicenses[] = $licenseKey;

            $licenseAuditData = [
                'license_id' => $licenseKey->id,
                'license_audit_uuid' => Uuid::generate(4),
                'entry_type' => 'LICENSE_DELETE',
                'license_key' => $licenseKey->license_key,
                'mac_address' => $licenseKey->mac_address,
                'user_id' => auth()->user()->id
            ];

            LicenseAudit::insertRecord($licenseAuditData);
        }

        else if ($data['delete_type'] == 'PACKAGE')
        {
            $productLicenseKeys = ProductLicenseKeys::where([
                'license_key' => $existProductLicenseKey->license_key,
                'package_id' => $existProductLicenseKey->package_id
            ])->get();

            foreach ($productLicenseKeys as $productLicenseKey)
            {
                $updateData = ['updated_by' => auth()->user()->id];
                $licenseKey = ProductLicenseKeys::updateAndDeleteRecord($updateData, $productLicenseKey->license_uuid);
                $licenseKey = LicenseKeyService::getLicenseRelationalData($licenseKey);
                $deletedLicenses[] = $licenseKey;

                $licenseAuditData = [
                    'license_id' => $licenseKey->id,
                    'license_audit_uuid' => Uuid::generate(4),
                    'entry_type' => 'LICENSE_DELETE',
                    'license_key' => $licenseKey->license_key,
                    'mac_address' => $licenseKey->mac_address,
                    'user_id' => auth()->user()->id
                ];

                LicenseAudit::insertRecord($licenseAuditData);
            }
        }

        if (!empty($deletedLicenses))
        {
            return json_encode(["status" => true, "code" => 200, "message" => "License Key Deleted Successfully", "data" => $deletedLicenses, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => "", "status_code" => 422]);
        }
    }


    public static function getLicenseHistoryData($data)
    {
        $existProductLicenseKey = ProductLicenseKeys::where('license_uuid', $data['id'])->first();

        $licenseAudit = LicenseAudit::where('license_id', $existProductLicenseKey->id)->orderBy('created_at', 'DESC')->get();
        $licenseAudit->makeHidden(['id', 'license_key']);

        $macHistoryUserInfo = $licenseAudit->map( function($license) {
            if (!empty($license->user))
            {
                return $license->user->makeHidden(['id']);
            }
            else
            {
                return $license->user;
            }
        });

        if (!empty($licenseAudit))
        {
            return json_encode(["status" => true, "code" => 200, "message" => "License History Retrieved Successfully", "data" => $licenseAudit, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => "", "status_code" => 422]);
        }
    }


    public static function getLicenseDetailsData($data, $user=null)
    {
        $hideLicenseKey=true;
        if(in_array(@$user->email, self::SHOW_LICENSE_KEY)){
            $hideLicenseKey=false;
        }
        $licenseKey = ProductLicenseKeys::where('license_uuid', $data['id'])->first();
        $licenseKey = LicenseKeyService::getLicenseRelationalData($licenseKey, $hideLicenseKey);

        if (!empty($licenseKey))
        {
            return json_encode(["status" => true, "code" => 200, "message" => "License Details Retrieved Successfully", "data" => $licenseKey, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => "", "status_code" => 422]);
        }
    }


    public static function licenseRenewalProcess($data, $user=null)
    {
        $renewedLicenses = [];
        $existProductLicenseKey = ProductLicenseKeys::where('license_uuid', $data['id'])->first();
        $hideLicenseKey=true;
        if(in_array(@$user->email, self::SHOW_LICENSE_KEY)){
            $hideLicenseKey=false;
        }

        if ($data['renewal_type'] == 'PRODUCT_CODE')
        {
            if (in_array($existProductLicenseKey->status, ['PURCHASED', 'EXPIRED', 'AVAILABLE']))
            {
                $licenseType = LicenseType::where([
                    'code' => $data['license_code'],
                    'status' => 'AVAILABLE'
                ])->first();

                $licenseProductData = [
                    'type_id' => $licenseType->id,
                    'expiry_duration' => $licenseType->expiry_duration,
                    'duration_type' => $licenseType->duration_type,
                    'updated_by' => auth()->user()->id,
                ];

                $licenseProducts = LicenseProduct::updateRecord($licenseProductData, $existProductLicenseKey->license_type_id);

                $updateData = [
                    'renewal_license_id' => $existProductLicenseKey->id,
                    'license_type' => $licenseType->code,
                    // 'status' => 'PURCHASED',
                    'status' => (@$existProductLicenseKey->status == 'AVAILABLE')? 'AVAILABLE' : 'PURCHASED',
                    'updated_by' => auth()->user()->id
                ];

                $expiry_duration = str_replace(['(', ')'], ['', ''], $licenseType->expiry_duration);

                if (strtotime($existProductLicenseKey->expiry_date) > 0 && date('Y-m-d H:i:s', strtotime($existProductLicenseKey->expiry_date)) < date('Y-m-d H:i:s') && $existProductLicenseKey->status != 'DEACTIVATED')
                {
                    $updateData['expiry_date'] =  date('Y-m-d H:i:s', strtotime($expiry_duration));
                }

                else if (strtotime($existProductLicenseKey->expiry_date) > 0 && date('Y-m-d H:i:s', strtotime($existProductLicenseKey->expiry_date)) > date('Y-m-d H:i:s') && $existProductLicenseKey->status != 'DEACTIVATED')
                {
                    $updateData['expiry_date'] =  date('Y-m-d H:i:s', strtotime($expiry_duration, strtotime($existProductLicenseKey->expiry_date)));
                }


                $renewedLicenseData = ProductLicenseKeys::updateRecord($updateData, $data['id']);

                $insertData = [
                    'license_id' => $existProductLicenseKey->id,
                    'license_audit_uuid' => Uuid::generate(4),
                    'entry_type' => 'LICENSE_RENEWAL',
                    'license_key' => $existProductLicenseKey->license_key,
                    'mac_address' => $renewedLicenseData->mac_address,
                    'previous_license_code' => $existProductLicenseKey->license_type,
                    'current_license_code' => $renewedLicenseData->license_type,
                    'expiry_duration' => $licenseType->expiry_duration,
                    'previous_expiry_date' => $existProductLicenseKey->expiry_date,
                    'current_expiry_date' => $renewedLicenseData->expiry_date,
                    'user_id' => auth()->user()->id
                ];

                LicenseAudit::insertRecord($insertData);

                $renewedLicenseData = LicenseKeyService::getLicenseRelationalData($renewedLicenseData, $hideLicenseKey);
                $renewedLicenses[] = $renewedLicenseData;
            }
        }

        else if ($data['renewal_type'] == 'PACKAGE')
        {
            $licenseType = LicenseType::where([
                'code' => $data['license_code'],
                'status' => 'AVAILABLE'
            ])->first();

            $licenseProductData = [
                'type_id' => $licenseType->id,
                'duration_type' => $licenseType->duration_type,
                'expiry_duration' => $licenseType->expiry_duration,
                'updated_by' => auth()->user()->id,
            ];

            $productLicenseKeys = ProductLicenseKeys::where([
                'license_key' => $existProductLicenseKey->license_key,
                'package_id' => $existProductLicenseKey->package_id
            ])->get();

            foreach ($productLicenseKeys as $productLicenseKey)
            {
                if (in_array($productLicenseKey->status, ['PURCHASED', 'EXPIRED', 'AVAILABLE']))
                {
                    $licenseProducts = LicenseProduct::updateRecord($licenseProductData, $productLicenseKey->license_type_id);

                    $updateData = [
                        'renewal_license_id' => $productLicenseKey->id,
                        'license_type' => $licenseType->code,
                        // 'status' => 'PURCHASED',
                        'status' => (@$productLicenseKey->status == 'AVAILABLE') ? 'AVAILABLE' : 'PURCHASED',
                        'updated_by' => auth()->user()->id
                    ];

                    $expiry_duration = str_replace(['(', ')'], ['', ''], $licenseType->expiry_duration);

                    if (strtotime($productLicenseKey->expiry_date) > 0 && date('Y-m-d H:i:s', strtotime($productLicenseKey->expiry_date)) < date('Y-m-d H:i:s') && $productLicenseKey->status != 'DEACTIVATED')
                    {
                        $updateData['expiry_date'] =  date('Y-m-d H:i:s', strtotime($expiry_duration));
                    }

                    else if (strtotime($productLicenseKey->expiry_date) > 0 && date('Y-m-d H:i:s', strtotime($productLicenseKey->expiry_date)) > date('Y-m-d H:i:s') && $productLicenseKey->status != 'DEACTIVATED')
                    {
                        $updateData['expiry_date'] =  date('Y-m-d H:i:s', strtotime($expiry_duration, strtotime($productLicenseKey->expiry_date)));
                    }

                    $renewedLicenseData = ProductLicenseKeys::updateRecord($updateData, $productLicenseKey->license_uuid);

                    $insertData = [
                        'license_id' => $productLicenseKey->id,
                        'license_audit_uuid' => Uuid::generate(4),
                        'entry_type' => 'LICENSE_RENEWAL',
                        'license_key' => $productLicenseKey->license_key,
                        'mac_address' => $renewedLicenseData->mac_address,
                        'previous_license_code' => $productLicenseKey->license_type,
                        'current_license_code' => $renewedLicenseData->license_type,
                        'expiry_duration' => $licenseType->expiry_duration,
                        'previous_expiry_date' => $productLicenseKey->expiry_date,
                        'current_expiry_date' => $renewedLicenseData->expiry_date,
                        'user_id' => auth()->user()->id
                    ];

                    LicenseAudit::insertRecord($insertData);

                    $renewedLicenseData = LicenseKeyService::getLicenseRelationalData($renewedLicenseData, $hideLicenseKey);
                    $renewedLicenses[] = $renewedLicenseData;
                }
            }

        }

        if ($renewedLicenses)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "License Key Renewal Successfully", "data" => $renewedLicenses, "status_code" => 200]);
        }

    }


    public static function generateLicense($data, $licenseKeyIdFlag = false)
    {
        $productLicenseKeys = [];

        $licenseType = LicenseType::where([
            'code' => $data['license_code'],
            'status' => 'AVAILABLE'
        ])->first();

        if (@$data['entity_type'] == 'PRODUCT')
        {
            $product = Product::where([
                'id' => @$data['entity_ref_id'],
                'status' => 'ACTIVE'
            ])->first();

            if (empty($product))
            {
                return json_encode(["status" => false, "code" => 422,"message" => "Data Not Found", "data" => ["error" => ["The given product code is not found"]], "status_code" => 422]);
            }
        }
        else if (@$data['entity_type'] == 'PACKAGE')
        {
            $package = Package::where([
                'id' => @$data['entity_ref_id'],
                'status' => 'AVAILABLE'
            ])->first();

            if (empty($package))
            {
                return json_encode(["status" => false, "code" => 422, "message" => "Data Not Found", "data" => ["error" => ["The given package is not found"]], "status_code" => 422]);
            }
        }
        else
        {
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => "", "status_code" => 422]);
        }

        if (empty($licenseType))
        {
            return json_encode(["status" => false, "code" => 422, "message" => "Data Not Found", "data" => ["error" => ["The given license code is not found"]], "status_code" => 422]);
        }
        else if ($licenseType->duration_type == 'DATE' && date('d-m-Y', strtotime($licenseType->expiry_duration)) <= date('d-m-Y') )
        {
            return json_encode(["status" => false, "code" => 422, "message" => "Request Denied", "data" => ["error" => ["The given license code is invalid"]], "status_code" => 422]);
        }

        $i = 1;
        if(empty($data['quantity'])){
            $data['quantity']=1;
        }
        for($i=1;$i<=$data['quantity'];$i++){
            // Make sure avoid LicenseKey duplication
            do {
                $newLicenseKey = LicenseKeyHelper::create();
                $checkExistence = ProductLicenseKeys::where('license_key', $newLicenseKey)->first();

                if (empty($checkExistence))
                    $newLicenseKey = $newLicenseKey;
                else
                    $newLicenseKey = '';

            } while (empty($newLicenseKey));

            if (!empty($product))
            {
                $licenseProductData = [
                    'type_id' => $licenseType->id,
                    'product_id' => $product->id,
                    'duration_type' => $licenseType->duration_type,
                    'expiry_duration' => $licenseType->expiry_duration,
                    'status' => 'AVAILABLE',
                    //'created_by' => auth()->user()->id,
                ];

                $licenseProducts = LicenseProduct::insertRecord($licenseProductData);

                $expiryDate = self::generateExpiryDate($licenseProducts->expiry_duration, $expiry_date=null);

                $productLicenseKeysData = [
                    'license_type_id' => $licenseProducts->id,
                    'license_uuid' => Uuid::generate(4),
                    'product_id' => $product->id,
                    'license_type' => $licenseType->code,
                    'license_key' => (@$data['license_key']) ? @$data['license_key']: $newLicenseKey,
                    'order_id' => @$data['order_id'],
                    'customer_id' => @$data['customer_id'],
                    'wp_order_item_id' => @$data['wp_order_item_id'],
                    'expiry_date' => $expiryDate,
                    'status' => 'AVAILABLE',
                    //'created_by' => auth()->user()->id,
                ];
                $productLicenseKeysModel = ProductLicenseKeys::insertRecord($productLicenseKeysData);
                $productLicenseKeysModel = ProductLicenseKeys::find($productLicenseKeysModel->id);

                if (!empty($productLicenseKeysModel->licenseProduct))
                {
                    $productLicenseKeysModel->licenseProduct->makeHidden(['id']);
                }
                $licenseKeyModel = LicenseKeyService::getLicenseRelationalData($productLicenseKeysModel, false, $licenseKeyIdFlag);
                // $productLicenseKeys[] = $licenseKeyModel;
                $productLicenseKeys[] = [
                    "type"=>'PRODUCT',
                    "details"=>$licenseKeyModel
                ];
            }
            else if (!empty($package))
            {
                $productCodes = json_decode($package->product_codes);
                $package_products=[];
                foreach ($productCodes as $productCode)
                {
                    $getProduct = Product::where([
                        'product_code' => $productCode,
                        'status' => 'ACTIVE'
                    ])->first();

                    if (!empty($getProduct))
                    {
                        $licenseProductData = [
                            'type_id' => $licenseType->id,
                            'product_id' => $getProduct->id,
                            'package_id' => $package->id,
                            'duration_type' => $licenseType->duration_type,
                            'expiry_duration' => $licenseType->expiry_duration,
                            "status" => 'AVAILABLE',
                            //'created_by' => auth()->user()->id,
                        ];

                        $licenseProducts = LicenseProduct::insertRecord($licenseProductData);
                        $expiryDate = self::generateExpiryDate($licenseProducts->expiry_duration, $expiry_date=null);


                        $productLicenseKeysData = [
                            'license_type_id' => $licenseProducts->id,
                            'license_uuid' => Uuid::generate(4),
                            'product_id' => $getProduct->id,
                            'package_id' => $package->id,
                            'license_type' => $licenseType->code,
                            'license_key' => $newLicenseKey,
                            'order_id' => @$data['order_id'],
                            'customer_id' => @$data['customer_id'],
                            'wp_order_item_id' => @$data['wp_order_item_id'],
                            'expiry_date' => $expiryDate,
                            'status' => 'AVAILABLE',
                        ];
                        $productLicenseKeysModel = ProductLicenseKeys::insertRecord($productLicenseKeysData);
                        $productLicenseKeysModel = ProductLicenseKeys::find($productLicenseKeysModel->id);

                        $package_products[] = LicenseKeyService::getLicenseRelationalData($productLicenseKeysModel, false);

                    }
                }
                $productLicenseKeys[] = [
                    "type"=>'PACKAGE',
                    "details"=>$package_products
                ];
            }
        }
        // } while ( (key_exists('quantity', $data)) ? $data['quantity'] >= $i : false);

        if ($productLicenseKeys)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "License Key Generated Successfully", "data" => $productLicenseKeys, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500]);
        }

    }


    private static function getLicenseRelationalData($licenseKey, $hide_license_key = true, $licenseKeyIdFlag = false)
    {
        if (!empty($licenseKey) && !$licenseKeyIdFlag)
        {
            if ($hide_license_key)
            {
                $licenseKey->makeHidden(['license_key', 'id']);
            }
            else
            {
                $licenseKey->makeHidden(['id']);
            }
        }

        if (!empty($licenseKey->customer))
        {
            $licenseKey->customer->makeHidden(['id']);
        }
        else
        {
            $licenseKey->customer;
        }

        if (!empty($licenseKey->order))
        {
            $licenseKey->order->makeHidden(['id']);
        }
        else
        {
            $licenseKey->order;
        }

        if (!empty($licenseKey->product))
        {
            $licenseKey->product->makeHidden(['id']);
        }
        else
        {
            $licenseKey->product;
        }

        if (!empty($licenseKey->package))
        {
            $licenseKey->package->makeHidden(['id']);
        }
        else
        {
            $licenseKey->package;
        }

        if (!empty($licenseKey->licenseProduct))
        {
            $licenseKey->licenseProduct->makeHidden(['id']);
        }
        else
        {
            $licenseKey->licenseProduct;
        }

        return $licenseKey;
    }

    public static function renew_existing_license_key($data,$user)
    {
        $orderItemIds = $data['order_item_id'];
        $renewedLicenses = [];
        $productLicenses = ProductLicenseKeys::whereIn('wp_order_item_id', function($query) use ($orderItemIds) {
            $query->select('id')
                  ->from('order_items')
                  ->whereIn('order_item_uuid', $orderItemIds);
        })
        ->join('license_types', 'product_license_keys.license_type', '=', 'license_types.code')
        ->where('license_types.status', 'AVAILABLE')
        ->select('product_license_keys.id as license_keys_id', 'product_license_keys.*', 'license_types.*')
        ->get();
        if(!empty($productLicenses))
        {
            foreach ($productLicenses as $productLicense) {
                $licenseProductData = [
                    'type_id' => $productLicense->id,
                    'expiry_duration' => $productLicense->expiry_duration,
                    'duration_type' => $productLicense->duration_type,
                    'updated_by' => @$user->id,
                ];

                $licenseProducts = LicenseProduct::updateRecord($licenseProductData, $productLicense->license_type_id);

                $expiryDate = self::generateExpiryDate($licenseProducts->expiry_duration, $productLicense->expiry_date);

                $updateData = [
                    'renewal_license_id' => $productLicense->license_keys_id,
                    'license_type' => $productLicense->code,
                    'expiry_date' =>$expiryDate,
                    'updated_by' => @$user->id
                ];

                if($productLicense->status === 'EXPIRED'){
                    $updateData['status'] = 'PURCHASED';
                }

                $expiry_duration = str_replace(['(', ')'], ['', ''], $productLicense->expiry_duration);

                if (strtotime($productLicense->expiry_date) > 0 && date('Y-m-d H:i:s', strtotime($productLicense->expiry_date)) < date('Y-m-d H:i:s') && $productLicense->status != 'DEACTIVATED')
                {
                    $updateData['expiry_date'] =  date('Y-m-d H:i:s', strtotime($expiry_duration));
                }

                else if (strtotime($productLicense->expiry_date) > 0 && date('Y-m-d H:i:s', strtotime($productLicense->expiry_date)) > date('Y-m-d H:i:s') && $productLicense->status != 'DEACTIVATED')
                {
                    $updateData['expiry_date'] =  date('Y-m-d H:i:s', strtotime($expiry_duration, strtotime($productLicense->expiry_date)));
                }
                $renewedLicenseData = ProductLicenseKeys::updateRecord($updateData, $productLicense->license_uuid);
                $insertData = [
                    'license_id' => $productLicense->license_keys_id,
                    'license_audit_uuid' => Uuid::generate(4),
                    'entry_type' => 'LICENSE_RENEWAL',
                    'license_key' => $productLicense->license_key,
                    'mac_address' => $renewedLicenseData->mac_address,
                    'previous_license_code' => $productLicense->license_type,
                    'current_license_code' => $renewedLicenseData->license_type,
                    'expiry_duration' => $productLicense->expiry_duration,
                    'previous_expiry_date' => $productLicense->expiry_date,
                    'current_expiry_date' => $renewedLicenseData->expiry_date,
                    'user_id' => @$user->id
                ];

                LicenseAudit::insertRecord($insertData);

                $renewedLicenseData = LicenseKeyService::getLicenseRelationalData($renewedLicenseData);
                $renewedLicenses[] = $renewedLicenseData;
            }
        }
        return $renewedLicenses;
    }

    public static function generateExpiryDate($duration, $expiryDate = null) {
        $todayDate = date('Y-m-d H:i:s');
        $expiryDuration = str_replace(['(', ')'], ['', ''], $duration);
        if (is_null($expiryDate)) {
            $newExpiryDate = date('Y-m-d H:i:s', strtotime($expiryDuration, strtotime($todayDate)));
        } 
        // elseif (strtotime($expiryDate) < strtotime($todayDate)) {
        //     $newExpiryDate = date('Y-m-d H:i:s', strtotime($expiryDuration, strtotime($todayDate)));
        // } 
        else {
            $newExpiryDate = date('Y-m-d H:i:s', strtotime($expiryDuration, strtotime($expiryDate)));
        }
        return $newExpiryDate;
    }
}
