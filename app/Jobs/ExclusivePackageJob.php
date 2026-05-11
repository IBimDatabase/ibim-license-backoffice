<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
//use Webpatser\Uuid\Uuid;
//use GoldSpecDigital\LaravelUuid\Uuid;
use Illuminate\Support\Str;// Added_by_Abdul_Rehman_for_Upgrade Laravel
use App\Models\Product;
use App\Models\ProductLicenseKeys;
use App\Models\LicenseType;
use App\Models\LicenseProduct;

class ExclusivePackageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $packageId;
    protected $productCodes;
    protected $removedProductCodes;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($productCodes, $removedProductCodes, $packageId)
    {
        $this->productCodes = $productCodes;
        $this->removedProductCodes = $removedProductCodes;
        $this->packageId = $packageId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $needToAddProducts = Product::whereIn('product_code', $this->productCodes)->get();
        $needToRemoveProducts = Product::whereIn('product_code', $this->removedProductCodes)->get();
        $licenseKeyModels = ProductLicenseKeys::where('package_id', $this->packageId)->get();
        if(!empty($needToAddProducts) && count($needToAddProducts)>0){
            foreach($licenseKeyModels as $key => $licenseKeyModel)
            {
                foreach($needToAddProducts as $key => $productModel)
                {
                    $existLicenseKey = ProductLicenseKeys::where([
                        'package_id' => $this->packageId,
                        'product_id' => $productModel->id,
                        'license_Key' => $licenseKeyModel->license_key
                    ])->first();
                    
                    if (empty($existLicenseKey))
                    {
                        $licenseType = LicenseType::where([
                            'code' =>  $licenseKeyModel->license_type,
                            'status' => 'AVAILABLE'
                        ])->first();

                        $licenseProductData = [
                            'type_id' => (isset($licenseType)) ? $licenseType->id : NULL,
                            'product_id' => $productModel->id,
                            'package_id' => $this->packageId,
                            'duration_type' => (isset($licenseType)) ? $licenseType->duration_type : NULL,
                            'expiry_duration' => (isset($licenseType)) ? $licenseType->expiry_duration : NULL,
                            "status" => 'AVAILABLE',
                            //'created_by' => auth()->user()->id,
                        ];
                
                        $licenseProducts = LicenseProduct::insertRecord($licenseProductData);

                        $licenseKeyActivated = ProductLicenseKeys::where('package_id', $this->packageId)->where('order_id', $licenseKeyModel->order_id)->where('license_key', $licenseKeyModel->license_key)->whereNotNull('expiry_date')->orderBy('expiry_date','asc')->first();

                        $productLicenseKeysData = [
                            'license_type_id' => (isset($licenseProducts)) ? $licenseProducts->id : NULL,
                            'license_uuid' => (string) Str::uuid(),
                            'product_id' => $productModel->id,
                            'package_id' => $this->packageId,
                            'license_type' => $licenseKeyModel->license_type,
                            'license_key' => $licenseKeyModel->license_key,
                            'order_id' => $licenseKeyModel->order_id,
                            'customer_id' => $licenseKeyModel->customer_id,
                            'status' => 'AVAILABLE',
                            'purchased_date' => date('Y-m-d H:i:s'),
                            // 'status' => (!empty(@$licenseKeyActivated->expiry_date)) ? 'PURCHASED' : 'AVAILABLE',
                            // 'purchased_date'=> (!empty(@$licenseKeyActivated->expiry_date)) ? date('Y-m-d H:i:s') : null,
                            'expiry_date'=> @$licenseKeyActivated->expiry_date
                            //'created_by' => auth()->user()->id,
                        ];
                        ProductLicenseKeys::insertRecord($productLicenseKeysData);
                    }
                }
                
            }
        }
        if(!empty($needToRemoveProducts) && count($needToRemoveProducts)>0){
            $needToRemoveProductIds = Product::whereIn('product_code', $this->removedProductCodes)->pluck('id')->toArray();
            if(!empty($needToRemoveProductIds)){
                ProductLicenseKeys::where('package_id', $this->packageId)->whereIn('product_id', $needToRemoveProductIds)->delete();
            }
        }
    }
}