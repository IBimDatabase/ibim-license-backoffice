<?php
namespace App\Cron;

use App\Models\ProductLicenseKeys;
use App\Models\LicenseProduct;

Class ExpiredLicenseCron
{
    public function __invoke()
    {
        $query = ProductLicenseKeys::where(function($q) {
            $q->where('expiry_date', '<=', date('Y-m-d H:i:s'))
            ->where('purchased_date', '!=', 0)->where('status', '!=', 'EXPIRED');
        });

        $query = $query->orWhereHas('licenseProduct', function($q) {
            $q->where('duration_type', '=', 'DATE');
            $q->where('expiry_duration', '<=',  date('Y-m-d'));
        });
            
        $expiredLicenses = $query->get();
        
        foreach($expiredLicenses as $expiredLicense)
        {
            ProductLicenseKeys::updateRecord(['status' => 'EXPIRED'], $expiredLicense->license_uuid);
        }
    }
}