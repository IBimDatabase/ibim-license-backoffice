<?php

namespace App\Console\Commands\DataMigration;

use Illuminate\Console\Command;
use DB;
use Storage;
use App\Models\Product;
use App\Models\LicenseType;
class ProductPricingUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-migration:product-update';

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

        $filename = Storage::disk('public_local')->path('files/templates/Product-List.csv');
        $product_list = self::csv_to_array($filename);
        $deleted_list=[];
        if(!empty($product_list)){
            foreach ($product_list as $key => $value) {
                if(!empty($value['Delete']) && $value['Delete']=="YES"){
                    $product_exist=Product::where('product_code', $value['Product Code'])->first();
                    if(!empty($product_exist)){
                        $deleted_list[]=$product_exist->id;
                    }
                }
            }
        }
        if(!empty($deleted_list)){
            Product::whereIn('id', $deleted_list)->delete();
        }
        $single_system_license=LicenseType::where('code', 'SINGLE_SYSTEM')->first();
        if(empty(@$single_system_license->id)){
            $single_system_license=LicenseType::insertRecord([
                'name' => "Single System",
                'code' => "SINGLE_SYSTEM",
                'duration_type' => "DURATION",
                'expiry_duration' => "50 Year(s)",
                'status' =>  "AVAILABLE",
            ]);
        }
        if(!empty($single_system_license)){
            $website_single_license = DB::connection('ibim_website')->table('license_type')->where('code', 'SINGLE_SYSTEM')->first();
            if(empty(@$website_single_license->id)){
                DB::connection('ibim_website')->table('license_type')->insert(
                    [[
                        'name' => "Single System",
                        'code' => "SINGLE_SYSTEM",
                        'duration_type' => "DURATION",
                        'expiry_duration' => "50 Year(s)",
                        'status' =>  "AVAILABLE",
                        'created_at' =>  "AVAILABLE",
                        'status' =>  "AVAILABLE",
                    ]]
                );
            }
        }

        $website_licenses = DB::connection('ibim_website')->table('license_type')->whereIn('code', ['SINGLE_SYSTEM', 'ANNUAL', 'LIFETIME', 'MONTHLY'])->get();        
        
        $licenses_info=[];
        if(!empty($website_licenses)){
            foreach ($website_licenses as $key => $value) {
                $licenses_info[$value->code]=$value->id;
            }
        }
        $price_file = Storage::disk('public_local')->path('files/templates/Price-List.csv');
        $price_list = self::csv_to_array($price_file);
        
        if(!empty($price_list)){
            $progressBar = $this->output->createProgressBar(count($price_list));
            $progressBar->start();
            foreach ($price_list as $key => $value) {
                $product_info = DB::connection('ibim_website')->table('products')
                ->where('product_code', $value['Product Code'])->first();
                if(!empty($product_info)){
                    foreach ($licenses_info as $key_1 => $value_1) {
                        $price=$value[$key_1];
                        $discounted_price=null;
                        if($key_1=="SINGLE_SYSTEM" && !empty($value['LIFETIME'])){
                            $discounted_price=$value['LIFETIME'];
                        }
                        $product_price = DB::connection('ibim_website')->table('product_prices')
                            ->where('product_id', $product_info->id)->where('license_id', $value_1)->first();
                            if(!empty($product_price) && !empty($value[$key_1])){
                                DB::connection('ibim_website')->table('product_prices')->where('id', $product_price->id)
                                ->update([
                                    "price"=>$price,
                                    "actual_price"=>$discounted_price,
                                ]);
                            } else if(!empty($value[$key_1])){
                                DB::connection('ibim_website')->table('product_prices')
                                ->insert([
                                    'product_id'=>$product_info->id,
                                    'created_at'=>date("Y-m-d H:i:s"),
                                    'updated_at'=>date("Y-m-d H:i:s"),
                                    'license_id'=>$value_1,
                                    "price"=>$price,
                                    "actual_price"=>$discounted_price,
                                ]);
                            }
                        }
                    }
                $progressBar->advance();
            }
            $progressBar->finish();
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
