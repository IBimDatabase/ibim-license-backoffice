<?php

use Illuminate\Database\Seeder;
use App\Models\LicenseType;
use Carbon\Carbon;

class LicenseTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $licenseTypes = [ 
            [
                'name' => 'Trial',
                'code' => 'TRIAL',
                'expiry_duration' => '7 Day(s)',
                'status' => 'AVAILABLE',
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'name' => 'Quarterly',
                'code' => 'QUARTERLY',
                'expiry_duration' => '3 Month(s)',
                'status' => 'AVAILABLE',
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'name' => 'Annual',
                'code' => 'ANNUAL',
                'expiry_duration' => '1 Year(s)',
                'status' => 'AVAILABLE',
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'name' => 'Lifetime',
                'code' => 'LIFETIME',
                'expiry_duration' => '50 Year(s)',
                'status' => 'AVAILABLE',
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
        ];

        foreach ($licenseTypes as $licenseType)
        {
            $existLicenseType = LicenseType::where('code', $licenseType['code'])->first();

            if (empty($existLicenseType))
            {
                LicenseType::insertRecord($licenseType);
            } 
        }
    }
}
