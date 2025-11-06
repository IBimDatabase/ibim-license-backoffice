<?php

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Helpers\ProductCodeHelper;

class ProductCodeAssignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = Product::where('product_code', NULL)
        ->orWhere('product_code', '')->get();
        
        if (count($products) > 0)
        {
            foreach ($products as $product)
            {
                $product_code = ProductCodeHelper::createCodeFromName(@$product->product_name);

                $updateData = [
                    "product_code" => $product_code,
                ];
                
                $productData = Product::updateRecord($updateData, @$product->product_uuid);
            }
        }
    }
}
