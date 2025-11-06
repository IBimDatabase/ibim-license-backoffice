<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LicenseProduct extends Model
{
    use SoftDeletes;

    protected $table = 'license_products';
    protected $fillable = ['type_id', 'product_id', 'expiry_duration', 'status', 'created_by', 'updated_by'];

    public static function insertRecord($data) 
    {
        $licenseProduct = new LicenseProduct();

        $licenseProduct->type_id = (key_exists('type_id', $data)) ? $data['type_id']: NULL;
        $licenseProduct->product_id = (key_exists('product_id', $data)) ? $data['product_id']: NULL;
        $licenseProduct->package_id = (key_exists('package_id', $data)) ? $data['package_id']: NULL;
        $licenseProduct->duration_type = (key_exists('duration_type', $data)) ? $data['duration_type']: NULL;
        $licenseProduct->expiry_duration = (key_exists('expiry_duration', $data)) ? $data['expiry_duration']: NULL;
        $licenseProduct->status = (key_exists('status', $data)) ? $data['status']: NULL;
        $licenseProduct->created_by = (key_exists('created_by', $data)) ? $data['created_by']: NULL;

        if ($licenseProduct->save())
            return $licenseProduct;
        else   
            return false;
    }

    public static function updateRecord($data, $id) 
    {
        $licenseProduct = LicenseProduct::find($id);

        $licenseProduct->type_id = (key_exists('type_id', $data)) ? $data['type_id']: $licenseProduct->type_id;
        $licenseProduct->product_id = (key_exists('product_id', $data)) ? $data['product_id']: $licenseProduct->product_id;
        $licenseProduct->package_id = (key_exists('package_id', $data)) ? $data['package_id']: $licenseProduct->package_id;
        $licenseProduct->duration_type = (key_exists('duration_type', $data)) ? $data['duration_type']: $licenseProduct->duration_type;
        $licenseProduct->expiry_duration = (key_exists('expiry_duration', $data)) ? $data['expiry_duration']: $licenseProduct->expiry_duration;
        $licenseProduct->status = (key_exists('status', $data)) ? $data['status']: $licenseProduct->status;
        $licenseProduct->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: $licenseProduct->updated_by;

        if ($licenseProduct->save())
            return $licenseProduct;
        else   
            return false;
    }

    public function licenseType()
    {
        return $this->hasOne(LicenseType::class, 'id', 'type_id')->withTrashed();
    }
}
