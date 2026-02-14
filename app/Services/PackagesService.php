<?php

namespace App\Services;

use App\Models\Package;
//use Webpatser\Uuid\Uuid;
//use GoldSpecDigital\LaravelUuid\Uuid;
use Illuminate\Support\Str;// Added_by_Abdul_Rehman_for_Upgrade Laravel
use Illuminate\Support\Facades\DB;
use App\Jobs\ExclusivePackageJob;
DB::enableQueryLog();

class PackagesService
{
    public static function getPackagesData($data, $perPage)
    {
        $query = new Package;

        if (!empty($data['package_name']))
            $query = $query->where('package_name', 'like', '%'. $data['package_name'] .'%');

        if (!empty($data['package_code']))
            $query = $query->where('package_code', 'like', '%'. $data['package_code'] .'%');

        if (!empty($data['product_codes']))
        {
            $productCodes = explode(',', trim($data['product_codes']));
            $query = $query->where(function($q) use ($productCodes) {
                foreach($productCodes as $key => $productCode)
                {
                    $q->where('product_codes', 'like', '%'. $productCode .'%');
                }
            });
                
        }
            
        if (!empty($data['status']))
            $query = $query->where('status', 'like', $data['status']);
            
        $query = $query->orderBy((@$data['sort_by']) ? @$data['sort_by'] : 'created_at', (@$data['sort_order']) ? @$data['sort_order'] : 'DESC');

        if (!key_exists('page', $data) || $data['page'] == 'all')
            $packages = $query->paginate($perPage);
        else
            $packages = $query->paginate($perPage);

        //dd (DB::getQueryLog());
        return json_encode(["status" => true, "code" => 200, "message" => 'Packages Retrieved Successfully', "data" => $packages, "status_code" => 200]);
    }


    public static function addPackageData($data)
    {
        $packageData = [
            'package_uuid' => (string) Str::uuid(),
            'package_name' => $data['package_name'],
            'package_code' => $data['package_code'],
            'product_codes' => json_encode($data['product_codes']),
            'exclusive_package' => (!empty($data['exclusive_package']) && strtoupper($data['exclusive_package'])=='YES')?'YES':'NO', 
            'status' => $data['status'],
            'created_by' => auth()->user()->id,
        ];

        $package = Package::insertRecord($packageData);
        
        // To fixing, malformed UTF-8 characters
        $package = Package::find($package->id);

        if ($package)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "Package Added Successfully", "data" => $package, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500]);
        }
    }


    public static function updatePackageData($data)
    {
        $packageData = [
            'package_name' => $data['package_name'],
            'package_code' => $data['package_code'],
            'product_codes' => json_encode($data['product_codes']),
            'exclusive_package' => (!empty($data['exclusive_package']) && strtoupper($data['exclusive_package'])=='YES')?'YES':'NO', 
            'status' => $data['status'],
            'updated_by' => auth()->user()->id,
        ];

        $checkDuplication = Package::where('package_uuid', '!=', $data['id'])->where(function($q) use ($data) {
            $q->where('package_name', $data['package_name']);
            $q->orWhere('package_code', $data['package_code']);
        })->first();

        if (!empty($checkDuplication))
        {
            $error = ["error" => ["The package name or package code has already been taken."]];
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error, "status_code" => 422]);
        }

        $existingPackage = Package::where('package_uuid', $data['id'])->first();
        $productCodesArray = json_decode($existingPackage->product_codes, true);

        $removedProductCodes = array_diff($productCodesArray, $data['product_codes']);
        
        if (@$packageData['exclusive_package'] == 'YES')
        {
            ExclusivePackageJob::dispatch($data['product_codes'], $removedProductCodes, $existingPackage->id);
        }

        $package = Package::updateRecord($packageData, $data['id']);

        if ($package)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "Package Updated Successfully", "data" => $package, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500]);
        }
        
    }

}
