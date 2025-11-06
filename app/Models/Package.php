<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use SoftDeletes;

    protected $table = 'packages';
    protected $fillable = [
        'package_name', 'package_code', 'product_codes', 'package_uuid', 'status', 'created_by', 'updated_by'
    ];

    public static function insertRecord($data) 
    {
        $package = new Package();

        $package->package_name = (key_exists('package_name', $data)) ? $data['package_name']: NULL;
        $package->package_code = (key_exists('package_code', $data)) ? $data['package_code']: NULL;
        $package->product_codes = (key_exists('product_codes', $data)) ? $data['product_codes']: NULL;
        $package->package_uuid = (key_exists('package_uuid', $data)) ? $data['package_uuid']: NULL;
        $package->status = (key_exists('status', $data)) ? $data['status']: NULL;
        $package->exclusive_package = (key_exists('exclusive_package', $data)) ? $data['exclusive_package']: NULL;
        $package->created_by = (key_exists('created_by', $data)) ? $data['created_by']: NULL;

        if ($package->save())
            return $package;
        else   
            return false;
    }

    public static function updateRecord($data, $id) 
    {
        $package = Package::where('package_uuid', $id)->first();

        $package->package_name = (key_exists('package_name', $data)) ? $data['package_name']: $package->package_name;
        $package->package_code = (key_exists('package_code', $data)) ? $data['package_code']: $package->package_code;
        $package->product_codes = (key_exists('product_codes', $data)) ? $data['product_codes']: $package->product_codes;
        $package->status = (key_exists('status', $data)) ? $data['status']: $package->status;
        $package->exclusive_package = (key_exists('exclusive_package', $data)) ? $data['exclusive_package']: $package->exclusive_package;
        $package->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: $package->updated_by;

        if ($package->save())
            return $package;
        else   
            return false;
    }

    public function productInfo() {
        return $this->hasMany(ProductDetail::class, 'id', 'product_id');
    }
}
