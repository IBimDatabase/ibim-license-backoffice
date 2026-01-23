<?php

namespace App\Services;

use App\Models\LicenseType;
use App\Helpers\LicenseKeyHelper;
//use Webpatser\Uuid\Uuid;
//use GoldSpecDigital\LaravelUuid\Uuid; // Added_by_Abdul_Rehman_for_Upgrade Laravel
use App\Imports\LicenseTypesImport;
use App\Services\WPProductAttributeService;
use Excel;
use Exception;

class LicenseTypeService
{
    public static function getType($data, $id=null)
    {
        LicenseTypeService::checkAvailability();
        
        $query = new LicenseType;

        if (!empty($data['name']))
            $query = $query->where('name', 'like', '%'. $data['name'] .'%');

        if (!empty($data['code']))
            $query = $query->where('code', 'like', '%'. $data['code'] .'%');
        
        if (!empty($data['expiry_duration']))
            $query = $query->where('expiry_duration', 'like', '%'. $data['expiry_duration'] .'%');
        
        if (!empty($id))
            $query = $query->where('id', $id);

        if (!empty($data['status']))
            $query = $query->where('status', 'like', $data['status']);

            $query = $query->orderBy((@$data['sort_by']) ? @$data['sort_by'] : 'created_at', (@$data['sort_order']) ? @$data['sort_order'] : 'DESC');

        if (!key_exists('page', $data) || $data['page'] == 'all')
            $licenseTypes = $query->paginate(1000);
        else
            $licenseTypes = $query->paginate(10);

        return json_encode(["status" => true, "code" => 200, "message" => 'License Types Retrieved Successfully', "data" => $licenseTypes, "status_code" => 200]);
    }


    public static function addType($data)
    {
        $licenseTypeData = [
            'name' => $data['name'],
            'code' => $data['code'],
            'description' => @$data['description'],
            'status' => $data['status'],
            'created_by' => auth()->user()->id,
        ];

        if (key_exists('expiry_duration_date', $data) && !empty($data['expiry_duration_date']))
        {
            $licenseTypeData['expiry_duration'] = date('Y-m-d', strtotime($data['expiry_duration_date']));
            $licenseTypeData['duration_type'] = 'DATE';
        }
        else
        {
            $licenseTypeData['expiry_duration'] = $data['expiry_duration']. ' ' .$data['expiry_period'];
            $licenseTypeData['duration_type'] = 'DURATION';
        }
        $result = LicenseType::insertRecord($licenseTypeData);
                
        if ($result instanceof LicenseType)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "License Type Added Successfully", "data" => $result, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 500, "message" => "Query Error", "data" => ["error" => $result], "status_code" => 500]);
        }   

        /** 
        $wooCommerceAttributes = WPProductAttributeService::getProductAttributesData(NULL);
        $wooCommerceAttributeId = NULL;

        if ($wooCommerceAttributes['status'] === true)
        {
            foreach ($wooCommerceAttributes['data'] as $wooCommerceAttribute)
            {
                if (strtolower(@$wooCommerceAttribute->slug) == 'pa_subscription')
                {
                    $wooCommerceAttributeId = @$wooCommerceAttribute->id;
                    break;
                }
            }

            if ($licenseTypeData['status'] == 'AVAILABLE')
            {
                $wooCommerceData = [
                    'name' => $licenseTypeData['name'],
                    'slug' => strtolower($licenseTypeData['code']),
                    'description' => @$licenseTypeData['description'],
                    'attribute_id' => $wooCommerceAttributeId
                ];
        
                $wcCreateTerms = WPProductAttributeService::createProductAttributeTermsData($wooCommerceData);
                
                if ($wcCreateTerms['status'] == true)
                {
                    $licenseTypeData['wp_attribute_term_id'] = @$wcCreateTerms['data']->id;
                }
                else
                {
                    return json_encode(["status" => $wcCreateTerms['status'], "code" => 422, "message" => "WooCommerce Exception Error", "data" => $wcCreateTerms['data'], "status_code" => 422]);
                }
            }

            $result = LicenseType::insertRecord($licenseTypeData);
                    
            if ($result instanceof LicenseType)
            {
                return json_encode(["status" => true, "code" => 200, "message" => "License Type Added Successfully", "data" => $result, "status_code" => 200]);
            }
            else
            {
                return json_encode(["status" => false, "code" => 500, "message" => "Query Error", "data" => ["error" => $result], "status_code" => 500]);
            }            
        }
        else
        {
            return json_encode(["status" => $wooCommerceAttributes['status'], "code" => 422, "message" => "WooCommerce Exception Error", "data" => $wooCommerceAttributes['data'], "status_code" => 422]);
        }
         */
        
    }


    public static function updateType($data)
    {
        $licenseTypeData = [
            'name' => $data['name'],
            //'code' => $data['code'],
            'description' => @$data['description'],
            'status' => $data['status'],
            'updated_by' => auth()->user()->id,
        ];

        if (key_exists('expiry_duration_date', $data) && !empty($data['expiry_duration_date']))
        {
            $licenseTypeData['expiry_duration'] = date('Y-m-d', strtotime($data['expiry_duration_date']));
            $licenseTypeData['duration_type'] = 'DATE';
        }
        else
        {
            $licenseTypeData['expiry_duration'] = $data['expiry_duration']. ' ' .$data['expiry_period'];
            $licenseTypeData['duration_type'] = 'DURATION';
        }

        $checkNameDuplication = LicenseType::where('id', '!=', $data['id'])->where(function($q) use ($data) {
            $q->where('name', $data['name']);
        })->first();

        if (!empty($checkNameDuplication))
        {
            $error = ["error" => ["The license type name has already been taken."]];
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error, "status_code" => 422]);
        }

        $existLicenseType = LicenseType::find($data['id']);

        if (!empty($existLicenseType))
        {

            $licenseType = LicenseType::updateRecord($licenseTypeData, $data['id']);

            if ($licenseType)
            {
                return json_encode(["status" => true, "code" => 200, "message" => "License Type Updated Successfully", "data" => $licenseType, "status_code" => 200]);
            }
            else
            {
                return json_encode(["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500]);
            }
            /**

            $wooCommerceAttributes = WPProductAttributeService::getProductAttributesData(NULL);
            $wooCommerceAttributeId = NULL;

            if ($wooCommerceAttributes['status'] === true)
            {
                foreach ($wooCommerceAttributes['data'] as $wooCommerceAttribute)
                {
                    if (strtolower(@$wooCommerceAttribute->slug) == 'pa_subscription')
                    {
                        $wooCommerceAttributeId = @$wooCommerceAttribute->id;
                        break;
                    }
                }
                
                if (!empty($existLicenseType->wp_attribute_term_id))
                {   
                    if ($licenseTypeData['status'] == 'AVAILABLE')
                    {
                        $wooCommerceData = [
                            'name' => $licenseTypeData['name'],
                            'description' => @$data['description'],
                            'attribute_id' => $wooCommerceAttributeId,
                            'term_id' => $existLicenseType->wp_attribute_term_id
                        ];
            
                        $termResult = WPProductAttributeService::updateProductAttributeTermsData($wooCommerceData);    
                        $licenseTypeData['wp_attribute_term_id'] = @$termResult['data']->id;
                    }
                    else
                    {
                        $wooCommerceData = [
                            'force' => 'true',
                            'attribute_id' => $wooCommerceAttributeId,
                            'term_id' => $existLicenseType->wp_attribute_term_id
                        ];
            
                        $termResult = WPProductAttributeService::deleteProductAttributeTermsData($wooCommerceData);
                        $licenseTypeData['wp_attribute_term_id'] = NULL;
                    }
                    
                }
                else
                {
                    $wooCommerceData = [
                        'name' => $licenseTypeData['name'],
                        'description' => @$data['description'],
                        'slug' => strtolower($data['code']),
                        'attribute_id' => $wooCommerceAttributeId
                    ];
            
                    $termResult = WPProductAttributeService::createProductAttributeTermsData($wooCommerceData);
                    $licenseTypeData['wp_attribute_term_id'] = @$termResult['data']->id;
                }
                
                if (true) // $termResult['status'] == 
                {
                    $licenseType = LicenseType::updateRecord($licenseTypeData, $data['id']);

                    if ($licenseType)
                    {
                        return json_encode(["status" => true, "code" => 200, "message" => "License Type Updated Successfully", "data" => $licenseType, "status_code" => 200]);
                    }
                    else
                    {
                        return json_encode(["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500]);
                    }
                }
                else
                {
                    return json_encode(["status" => $termResult['status'], "code" => 422, "message" => "WooCommerce Exception Error", "data" => $termResult['data'], "status_code" => 422]);
                }
            }
            else
            {
                return json_encode(["status" => $wooCommerceAttributes['status'], "code" => 422, "message" => "WooCommerce Exception Error", "data" => $wooCommerceAttributes['data'], "status_code" => 422]);
            } 
            */
        }
        else
        {
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => "", "status_code" => 422]);
        }
        
    }


    protected static function checkAvailability()
    {
        $licenseTypes = LicenseType::all();
        $todayDate = date('Y-m-d');

        foreach ($licenseTypes as $licenseType) {
            if ($licenseType->duration_type == 'DATE')
            {
                if ((strtotime($licenseType->expiry_duration) <= strtotime($todayDate)) && $licenseType->status != 'NOT_AVAILABLE')
                {
                    $licenseTypeData = [
                        'status' => 'NOT_AVAILABLE'
                    ];
            
                    LicenseType::updateRecord($licenseTypeData, $licenseType->id);
                }
            }
        }
    }


    public static function importLicenseTypeData($ImportFile)
    {
        $import = new LicenseTypesImport();
        $import->import($ImportFile);
        
        $errors = [];
        foreach ($import->failures() as $failure) {
            $errors[] = $failure->errors(); // Actual error messages from Laravel validator
        }

        if (count($errors) == 0)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "License Type Added Successfully", "data" => "", "status_code" => 200]);
        }
        else if (count($errors) > 0)
        {
            $errors = array_map("unserialize", array_unique(array_map("serialize", $errors)));
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => ["error" => $errors], "status_code" => 422]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500]);
        }
    }

    public static function deleteLicenseType($data)
    {
        try{
            $existLicenseType = LicenseType::find($data['id']);

            if (!empty($existLicenseType))
            {
                $wooCommerceAttributes = WPProductAttributeService::getProductAttributesData(NULL);
                $wooCommerceAttributeId = NULL;

                if ($wooCommerceAttributes['status'] === true)
                {
                    foreach ($wooCommerceAttributes['data'] as $wooCommerceAttribute)
                    {
                        if (strtolower(@$wooCommerceAttribute->slug) == 'pa_subscription')
                        {
                            $wooCommerceAttributeId = @$wooCommerceAttribute->id;
                            break;
                        }
                    }
                    
                    if (!empty($existLicenseType->wp_attribute_term_id))
                    {   
                        $wooCommerceData = [
                            'force' => 'true',
                            'attribute_id' => $wooCommerceAttributeId,
                            'term_id' => $existLicenseType->wp_attribute_term_id
                        ];
            
                        $termResult = WPProductAttributeService::deleteProductAttributeTermsData($wooCommerceData);
                        $licenseTypeData['wp_attribute_term_id'] = NULL;                        
                    }
                }
                $updateData['updated_by'] = auth()->user()->id;
                $result = LicenseType::updateAndDeleteRecord($updateData, @$data['id']);

                return json_encode(["status" => true, "code" => 200, "message" => "License Type Deleted Successfully", "data" => $result, "status_code" => 200]);
            }
            else
            {
                return json_encode(["status" => false, "code" => 422, "message" => "License Type Not Found", "data" => $result, "status_code" => 422]);
            }
        }
        catch (Exception $e)
        {
            $errors = $e->getMessage();
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => ["error" => $errors], "status_code" => 422]);
        }
    }

}
