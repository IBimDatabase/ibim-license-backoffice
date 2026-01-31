<?php

use Illuminate\Database\Seeder;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Support\Str;// Added_by_Abdul_Rehman_for_Upgrade Laravel

class PackagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $packages = [
            [
                'package_uuid' => (string) Str::uuid(),
                'package_name' => 'Ultra',
                'package_code' => 'ULTRA',
                'product_codes' => '["ADD_FROM_FACE_SYMBOL_FOR_PANELS_IN_GA_DRAWING", "BOLT_HOLE_GENERATOR", "DRAIN_HOLE"]',
                'status' => 'AVAILABLE',
                'created_at' => Carbon::now()->toDateTimeString(),
            ]
        ];

        foreach ($packages as $package)
        {
            $existPackage = Package::where('package_code', $package['package_code'])->first();

            if (empty($existPackage))
            {
                Package::insertRecord($package);
            } 
        }
    }
}
