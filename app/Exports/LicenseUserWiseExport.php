<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Services\LicenseKeyService;

class LicenseUserWiseExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $this->data['type'] = 'PACKAGE';
        $package_list = LicenseKeyService::get_license_list($this->data);
        // $decode_data = json_decode($package_list, true);
        $package_result = $package_list['licenses'];
        
        $this->data['type'] = 'PRODUCT';
        $product_list = LicenseKeyService::get_license_list($this->data);
        // $product_decode_data = json_decode($product_list, true);
        $product_result = $product_list['licenses'];

        $sheets = [];

        if (!empty($product_result)) {
            $sheets['Products'] = new ProductSheetExport($product_result);
        }

        if (!empty($package_result)) {
            $sheets['Packages'] = new PackageSheetExport($package_result);
        }

        return $sheets;
    }
}
