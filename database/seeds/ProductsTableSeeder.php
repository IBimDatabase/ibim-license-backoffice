<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ProductDetail;
use Carbon\Carbon;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $src = public_path().'/files/products/';
        
        if (is_dir($src))
		    $files = scandir($src);

        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $extension = pathinfo($src.$file, PATHINFO_EXTENSION);
                if ($extension == 'json') {
                    $content = json_decode(file_get_contents($src.$file));

                    $product_array = [
                        'product_name' => $content->product_name,
                        'product_code' => $content->product_code,
                        'product_uuid' => Uuid::generate(4),
                        'purpose' => json_encode($content->purpose),
                        'description' => json_encode($content->description),
                        'package_content' => json_encode($content->package_content),
                        'status' => $content->status,
                        'created_at' => Carbon::now()->toDateTimeString(),
                    ];
                    
                    $existProduct = Product::where('product_code', $product_array['product_code'])->first();

                    if (empty($existProduct))
                    {
                        $product = Product::insertRecord($product_array);
                        $product_id = $product->id;

                        // Get data for product_details table
                        $productDetails = [];
                        if (count($content->steps) > 0) {
                            foreach (range(1, count($content->steps)) as $sort_order) {
                                $productDetails[] = [
                                    'product_id' => $product_id,
                                    'info_type' => 'STEPS',
                                    'info_value' => $content->steps[$sort_order - 1],
                                    'sort_order' => $sort_order,
                                    'additional_info' => '',
                                    'status' => $content->status,
                                    'created_at' => Carbon::now()->toDateTimeString(),
                                ];
                            }
                        }

                        if (count($content->pd_benefits) > 0) {
                            foreach (range(1, count($content->pd_benefits)) as $sort_order) {
                                $productDetails[] = [
                                    'product_id' => $product_id,
                                    'info_type' => 'PD_BENEFITS',
                                    'info_value' => $content->pd_benefits[$sort_order - 1],
                                    'sort_order' => $sort_order,
                                    'additional_info' => '',
                                    'status' => $content->status,
                                    'created_at' => Carbon::now()->toDateTimeString(),
                                ];
                            }
                        }

                        if (count($content->pc_benefits) > 0) {
                            foreach (range(1, count($content->pc_benefits)) as $sort_order) {
                                $productDetails[] = [
                                    'product_id' => $product_id,
                                    'info_type' => 'PC_BENEFITS',
                                    'info_value' => $content->pc_benefits[$sort_order - 1],
                                    'sort_order' => $sort_order,
                                    'additional_info' => '',
                                    'status' => $content->status,
                                    'created_at' => Carbon::now()->toDateTimeString(),
                                ];
                            }
                        }

                        if (count($content->download_process) > 0) {
                            foreach (range(1, count($content->download_process)) as $sort_order) {
                                $productDetails[] = [
                                    'product_id' => $product_id,
                                    'info_type' => 'DOWNLOAD_PROCESS',
                                    'info_value' => $content->download_process[$sort_order - 1],
                                    'sort_order' => $sort_order,
                                    'additional_info' => '',
                                    'status' => $content->status,
                                    'created_at' => Carbon::now()->toDateTimeString(),
                                ];
                            }
                        }

                        if (count($content->installation_process) > 0) {
                            foreach (range(1, count($content->installation_process)) as $sort_order) {
                                $productDetails[] = [
                                    'product_id' => $product_id,
                                    'info_type' => 'INSTALLATION_PROCESS',
                                    'info_value' => $content->installation_process[$sort_order - 1],
                                    'sort_order' => $sort_order,
                                    'additional_info' => '',
                                    'status' => $content->status,
                                    'created_at' => Carbon::now()->toDateTimeString(),
                                ];
                            }
                        }

                        foreach ($productDetails as $productDetail)
                        {
                            ProductDetail::insertRecord($productDetail);
                        }
                    } 
                    
                }
            }
        }
        
    }
}
