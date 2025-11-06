<?php

namespace App\Console\Commands\DataMigration;

use Illuminate\Console\Command;
use DB;
use Storage;
use App\Models\Product;
use App\Models\LicenseType;
class PackagePriceUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-migration:package-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $package_price=[
            "ALL_TOOLS"=>599,
            "PRECAST_TOOLS"=>499,
            "RESIDENTIAL_TOOLS"=>399,
            "STEEL_DETAILING_TOOLS"=>499,
            "CHECKING"=>151,
            "DRAWING"=>151,
            "MODELLING_RESIDENTIAL"=>251,
            "MODELLING_STEEL_DETAILING"=>351,
        ];
        $package_list = DB::connection('ibim_website')->table('package')->get();
        if(!empty($package_list)){
            foreach ($package_list as $key => $value) {
                if(!empty($value->code) && !empty($package_price[$value->code])){
                    DB::connection('ibim_website')->table('package')->where('id', $value->id)
                    ->update([
                        "price"=>round($package_price[$value->code] * 0.6, 0),
                        "actual_price"=>$package_price[$value->code],
                    ]);
                }
            }
        }

        $active_package = DB::connection('ibim_website')->table('package')->get();
        if(!empty($active_package)){
            foreach($active_package as $key => $val){
                $package_info=DB::table('packages')->where('id', $val->ibim_package_id)->first();
                if(!empty($package_info) && !empty($package_info->product_codes)){
                    $product_codes=json_decode($package_info->product_codes, true);
                    if(!empty($product_codes)){
                        $product_ids = Product::whereIn('product_code', $product_codes)->pluck('product_uuid')->toArray();
                        $active_products = DB::connection('ibim_website')->table('products')->whereIn('ibim_product_uuid', $product_ids)->get();
                        // $product_ids = Product::whereIn('product_code', $product_codes)->pluck('product_uuid')->toArray();
                        if(!empty($active_products)){
                            // dd($active_products);
                            foreach ($active_products as $key => $value) {
                                $active_product_rel = DB::connection('ibim_website')->table('package_product_rel')->where('package_id', $val->id)->where('product_id', $value->id)->first();
                                if(empty(@$active_product_rel->id)){
                                    DB::connection('ibim_website')->table('package_product_rel')->insert(
                                        [
                                            [
                                                'package_id'=>$val->id,
                                                'product_id'=>$value->id,
                                                'created_at'=>date("Y-m-d H:i:s"),
                                                'updated_at'=>date("Y-m-d H:i:s"),
                                            ]
                                        ]

                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        return 0;
    }
    public static function csv_to_array($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            while (($row = fgetcsv($handle, 5000, $delimiter)) !== false)
            {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }
}
