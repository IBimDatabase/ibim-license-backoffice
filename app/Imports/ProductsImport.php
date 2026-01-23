<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
//use Webpatser\Uuid\Uuid;
//use GoldSpecDigital\LaravelUuid\Uuid; // Added_by_Abdul_Rehman_for_Upgrade Laravel

class ProductsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure, WithCalculatedFormulas
{
    use Importable, SkipsFailures;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function rules(): array
    {
        return [
            'product_name' => 'required|string|unique:App\Models\Product,product_name|max:150|regex:/^[^\W]/',
            'product_code' => 'required|string|unique:App\Models\Product,product_code|max:150|regex:/^[^\W]/',
            'product_prefix' => 'required|string|max:150',
            'description' => 'nullable|string',
            'status' => 'required|in:"ACTIVE", "INACTIVE"|max:30',
        ];
    }

    public function collection(Collection $rows)
    {
        foreach($rows as $row) {
            if($row->filter()->isNotEmpty()){

                $lastProduct = Product::where('product_prefix', @$row['product_prefix'])->orderBy('id', 'DESC')->first();

                if (!empty($lastProduct))
                {
                    $productNumber = $lastProduct->product_number + 1;
                }
                else
                {
                    $productNumber = 1001;
                }

                if (@$row['description'] !== NULL)
                {
                    $description = json_encode([["Type" => "Text", "Content" => [@$row['description']]]]);
                }
                else
                {
                    $description = NULL;
                }
                
                Product::insertRecord([
                    'product_uuid' => Uuid::generate(4),
                    'product_name' => @$row['product_name'],
                    'product_code' => @$row['product_code'],
                    'product_prefix' => @$row['product_prefix'],
                    'product_number' => $productNumber,
                    'product_id' =>  @$row['product_prefix']. '-' .$productNumber,
                    'description' => $description,
                    'status' => @$row['status'],
                    'created_by' => auth()->user()->id,
                ]);
            }
   	    }	
    }
}
