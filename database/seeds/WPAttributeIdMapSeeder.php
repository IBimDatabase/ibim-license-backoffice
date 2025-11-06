<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\LicenseType;
use App\Services\WPProductAttributeService;

class WPAttributeIdMapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
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
            
            $wooCommerceAttributeTerms = WPProductAttributeService::getProductAttributeTermsData(['attribute_id' => $wooCommerceAttributeId]);
            
            if ($wooCommerceAttributeTerms['status'] === true)
            {
                foreach ($wooCommerceAttributeTerms['data'] as $wooCommerceAttributeTerm)
                {
                    $existLicenseType = LicenseType::where('code', strtoupper(@$wooCommerceAttributeTerm->slug))->first();
                    
                    if ($existLicenseType)
                    {
                        $updateData = [
                            'wp_attribute_term_id' => @$wooCommerceAttributeTerm->id
                        ];
                        
                        LicenseType::updateRecord($updateData, $existLicenseType->id);
                    }
                }
            }            
            else
            {
                return $wooCommerceAttributeTerms;
            }
        }
            
    }
}
