<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductDetail extends Model
{
    use SoftDeletes;

    protected $table = 'product_details';
    protected $fillable = [
        'product_id', 'info_type', 'info_value', 'sort_order', 'additional_info', 'status', 'created_by', 'updated_by'
    ];

    public static function insertRecord($data) 
    {
        $productDetail = new ProductDetail();

        $productDetail->product_id = (key_exists('product_id', $data)) ? $data['product_id']: NULL;
        $productDetail->info_type = (key_exists('info_type', $data)) ? $data['info_type']: NULL;
        $productDetail->info_value = (key_exists('info_value', $data)) ? $data['info_value']: NULL;
        $productDetail->sort_order = (key_exists('sort_order', $data)) ? $data['sort_order']: NULL;
        $productDetail->additional_info = (key_exists('additional_info', $data)) ? $data['additional_info']: NULL;
        $productDetail->status = (key_exists('status', $data)) ? $data['status']: NULL;
        $productDetail->created_by = (key_exists('created_by', $data)) ? $data['created_by']: NULL;

        if ($productDetail->save())
            return $productDetail;
        else   
            return false;
    }

    public static function updateRecord($data, $id) 
    {
        $productDetail = ProductDetail::find($id);

        $productDetail->product_id = (key_exists('product_id', $data)) ? $data['product_id']: $productDetail->product_id;
        $productDetail->info_type = (key_exists('info_type', $data)) ? $data['info_type']: $productDetail->info_type;
        $productDetail->info_value = (key_exists('info_value', $data)) ? $data['info_value']: $productDetail->info_value;
        $productDetail->sort_order = (key_exists('sort_order', $data)) ? $data['sort_order']: $productDetail->sort_order;
        $productDetail->additional_info = (key_exists('additional_info', $data)) ? $data['additional_info']: $productDetail->additional_info;
        $productDetail->status = (key_exists('status', $data)) ? $data['status']: $productDetail->status;
        $productDetail->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: NULL;

        if ($productDetail->save())
            return $productDetail;
        else   
            return false;
    }

}
