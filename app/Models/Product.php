<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';
    protected $fillable = [
        'product_name', 'product_prefix', 'product_code', 'product_number', 'product_uuid', 'wp_product_id', 'purpose', 'description', 'package_content', 'status', 'created_by', 'updated_by'
    ];

    public static function insertRecord($data) 
    {
        $product = new Product();

        $product->product_name = (key_exists('product_name', $data)) ? $data['product_name']: NULL;
        $product->product_prefix = (key_exists('product_prefix', $data)) ? $data['product_prefix']: NULL;
        $product->product_number = (key_exists('product_number', $data)) ? $data['product_number']: NULL;
        $product->product_id = (key_exists('product_id', $data)) ? $data['product_id']: NULL;
        $product->product_code = (key_exists('product_code', $data)) ? $data['product_code']: NULL;
        $product->product_uuid = (key_exists('product_uuid', $data)) ? $data['product_uuid']: NULL;
        $product->wp_product_id = (key_exists('wp_product_id', $data)) ? $data['wp_product_id']: NULL;
        $product->purpose = (key_exists('purpose', $data)) ? $data['purpose']: NULL;
        $product->description = (key_exists('description', $data)) ? $data['description']: NULL;
        $product->package_content = (key_exists('package_content', $data)) ? $data['package_content']: NULL;
        $product->s3_file_path = (key_exists('s3_file_path', $data)) ? $data['s3_file_path']: NULL;
        $product->wp_product_json = (key_exists('wp_product_json', $data)) ? $data['wp_product_json']: NULL;
        $product->status = (key_exists('status', $data)) ? $data['status']: NULL;
        $product->created_by = (key_exists('created_by', $data)) ? $data['created_by']: NULL;

        if ($product->save())
            return $product;
        else   
            return false;
    }

    public static function updateRecord($data, $id) 
    {
        $product = Product::where('product_uuid', $id)->first();

        $product->product_name = (key_exists('product_name', $data)) ? $data['product_name']: $product->product_name;
        $product->product_prefix = (key_exists('product_prefix', $data)) ? $data['product_prefix']: $product->product_prefix;
        $product->product_number = (key_exists('product_number', $data)) ? $data['product_number']: $product->product_number;
        $product->product_id = (key_exists('product_id', $data)) ? $data['product_id']: $product->product_id;
        $product->wp_product_id = (key_exists('wp_product_id', $data)) ? $data['wp_product_id']: $product->wp_product_id;
        $product->product_code = (key_exists('product_code', $data)) ? $data['product_code']: $product->product_code;
        $product->purpose = (key_exists('purpose', $data)) ? $data['purpose']: $product->purpose;
        $product->description = (key_exists('description', $data)) ? $data['description']: $product->description;
        $product->package_content = (key_exists('package_content', $data)) ? $data['package_content']: $product->package_content;
        $product->s3_file_path = (key_exists('s3_file_path', $data)) ? $data['s3_file_path']: $product->s3_file_path;
        $product->wp_product_json = (key_exists('wp_product_json', $data)) ? $data['wp_product_json']: $product->wp_product_json;
        $product->status = (key_exists('status', $data)) ? $data['status']: $product->status;
        $product->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: $product->updated_by;

        if ($product->save())
            return $product;
        else   
            return false;
    }

    public static function updateAndDeleteRecord($data, $id) 
    {
        $product = Product::where('product_uuid', $id)->first();

        $product->product_name = (key_exists('product_name', $data)) ? $data['product_name']: $product->product_name;
        $product->product_prefix = (key_exists('product_prefix', $data)) ? $data['product_prefix']: $product->product_prefix;
        $product->product_number = (key_exists('product_number', $data)) ? $data['product_number']: $product->product_number;
        $product->product_id = (key_exists('product_id', $data)) ? $data['product_id']: $product->product_id;
        $product->wp_product_id = (key_exists('wp_product_id', $data)) ? $data['wp_product_id']: $product->wp_product_id;
        $product->product_code = (key_exists('product_code', $data)) ? $data['product_code']: $product->product_code;
        $product->purpose = (key_exists('purpose', $data)) ? $data['purpose']: $product->purpose;
        $product->description = (key_exists('description', $data)) ? $data['description']: $product->description;
        $product->package_content = (key_exists('package_content', $data)) ? $data['package_content']: $product->package_content;
        $product->s3_file_path = (key_exists('s3_file_path', $data)) ? $data['s3_file_path']: $product->s3_file_path;
        $product->wp_product_json = (key_exists('wp_product_json', $data)) ? $data['wp_product_json']: $product->wp_product_json;
        $product->status = (key_exists('status', $data)) ? $data['status']: $product->status;
        $product->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: $product->updated_by;

        if ($product->save())
        {
            $product->delete();
            return $product;
        }
        else   
        {
            return false;
        }
    }

    public function productInfo()
    {
        return $this->hasMany(ProductDetail::class, 'id', 'product_id');
    }
}
