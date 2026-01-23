<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\LicenseKeyHelper;
//use Webpatser\Uuid\Uuid;
//use GoldSpecDigital\LaravelUuid\Uuid; // Added_by_Abdul_Rehman_for_Upgrade Laravel

class ProductLicenseKeys extends Model
{
    use SoftDeletes;

    protected $table = 'product_license_keys';
    protected $fillable = [
        'license_type_id', 'product_id', 'renewal_license_id', 'license_uuid', 'license_type', 'license_key', 'mac_address', 'license_info', 'expiry_date', 'purchased_date', 'status', 'wp_order_item_id', 'customer_id', 'created_by', 'updated_by'
    ];
    protected $appends = ['hashed_license_key'];

    public static function insertRecord($data) 
    {
        $productLicenseKeys = new ProductLicenseKeys();

        $productLicenseKeys->license_type_id = (key_exists('license_type_id', $data)) ? $data['license_type_id']: NULL;
        $productLicenseKeys->product_id = (key_exists('product_id', $data)) ? $data['product_id']: NULL;
        $productLicenseKeys->package_id = (key_exists('package_id', $data)) ? $data['package_id']: NULL;
        $productLicenseKeys->renewal_license_id = (key_exists('renewal_license_id', $data)) ? $data['renewal_license_id']: NULL;
        $productLicenseKeys->license_uuid = (key_exists('license_uuid', $data)) ? $data['license_uuid']: Uuid::generate(4)->string;
        $productLicenseKeys->license_type = (key_exists('license_type', $data)) ? $data['license_type']: NULL;
        $productLicenseKeys->license_key = (key_exists('license_key', $data)) ? $data['license_key']: NULL;
        $productLicenseKeys->mac_address = (key_exists('mac_address', $data)) ? $data['mac_address']: NULL;
        $productLicenseKeys->license_info = (key_exists('license_info', $data)) ? $data['license_info']: NULL;
        $productLicenseKeys->expiry_date = (key_exists('expiry_date', $data)) ? $data['expiry_date']: NULL;
        $productLicenseKeys->purchased_date = (key_exists('purchased_date', $data)) ? $data['purchased_date']: NULL;
        $productLicenseKeys->status = (key_exists('status', $data)) ? $data['status']: NULL;
        $productLicenseKeys->wp_order_item_id = (key_exists('wp_order_item_id', $data)) ? $data['wp_order_item_id']: NULL;
        $productLicenseKeys->customer_id = (key_exists('customer_id', $data)) ? $data['customer_id']: NULL;
        $productLicenseKeys->order_id = (key_exists('order_id', $data)) ? $data['order_id']: NULL;
        $productLicenseKeys->created_by = (key_exists('created_by', $data)) ? $data['created_by']: NULL;

        if ($productLicenseKeys->save())
            return $productLicenseKeys;
        else   
            return false;
    }

    public static function updateRecord($data, $id) 
    {
        $productLicenseKeys = ProductLicenseKeys::where('license_uuid', $id)->first();

        $productLicenseKeys->license_type_id = (key_exists('license_type_id', $data)) ? $data['license_type_id']: $productLicenseKeys->license_type_id;
        $productLicenseKeys->product_id = (key_exists('product_id', $data)) ? $data['product_id']: $productLicenseKeys->product_id;
        $productLicenseKeys->package_id = (key_exists('package_id', $data)) ? $data['package_id']: $productLicenseKeys->package_id;
        $productLicenseKeys->renewal_license_id = (key_exists('renewal_license_id', $data)) ? $data['renewal_license_id']: $productLicenseKeys->renewal_license_id;
        $productLicenseKeys->license_uuid = (key_exists('license_uuid', $data)) ? $data['license_uuid']: $productLicenseKeys->license_uuid;
        $productLicenseKeys->license_type = (key_exists('license_type', $data)) ? $data['license_type']: $productLicenseKeys->license_type;
        $productLicenseKeys->license_key = (key_exists('license_key', $data)) ? $data['license_key']: $productLicenseKeys->license_key;
        $productLicenseKeys->mac_address = (key_exists('mac_address', $data)) ? $data['mac_address']: $productLicenseKeys->mac_address;
        $productLicenseKeys->license_info = (key_exists('license_info', $data)) ? $data['license_info']: $productLicenseKeys->license_info;
        $productLicenseKeys->expiry_date = (key_exists('expiry_date', $data)) ? $data['expiry_date']: $productLicenseKeys->expiry_date;
        $productLicenseKeys->purchased_date = (key_exists('purchased_date', $data)) ? $data['purchased_date']: $productLicenseKeys->purchased_date;
        $productLicenseKeys->status = (key_exists('status', $data)) ? $data['status']: $productLicenseKeys->status;
        $productLicenseKeys->wp_order_item_id = (key_exists('wp_order_item_id', $data)) ? $data['wp_order_item_id']: $productLicenseKeys->wp_order_item_id;
        $productLicenseKeys->customer_id = (key_exists('customer_id', $data)) ? $data['customer_id']: $productLicenseKeys->customer_id;
        $productLicenseKeys->order_id = (key_exists('order_id', $data)) ? $data['order_id']: $productLicenseKeys->order_id;
        $productLicenseKeys->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: $productLicenseKeys->updated_by;
        
        if ($productLicenseKeys->save())
            return $productLicenseKeys;
        else   
            return false;
    }

    public static function updateAndDeleteRecord($data, $id)
    {
        $productLicenseKeys = ProductLicenseKeys::where('license_uuid', $id)->first();

        $productLicenseKeys->license_type_id = (key_exists('license_type_id', $data)) ? $data['license_type_id']: $productLicenseKeys->license_type_id;
        $productLicenseKeys->product_id = (key_exists('product_id', $data)) ? $data['product_id']: $productLicenseKeys->product_id;
        $productLicenseKeys->package_id = (key_exists('package_id', $data)) ? $data['package_id']: $productLicenseKeys->package_id;
        $productLicenseKeys->renewal_license_id = (key_exists('renewal_license_id', $data)) ? $data['renewal_license_id']: $productLicenseKeys->renewal_license_id;
        $productLicenseKeys->license_uuid = (key_exists('license_uuid', $data)) ? $data['license_uuid']: $productLicenseKeys->license_uuid;
        $productLicenseKeys->license_type = (key_exists('license_type', $data)) ? $data['license_type']: $productLicenseKeys->license_type;
        $productLicenseKeys->license_key = (key_exists('license_key', $data)) ? $data['license_key']: $productLicenseKeys->license_key;
        $productLicenseKeys->mac_address = (key_exists('mac_address', $data)) ? $data['mac_address']: $productLicenseKeys->mac_address;
        $productLicenseKeys->license_info = (key_exists('license_info', $data)) ? $data['license_info']: $productLicenseKeys->license_info;
        $productLicenseKeys->expiry_date = (key_exists('expiry_date', $data)) ? $data['expiry_date']: $productLicenseKeys->expiry_date;
        $productLicenseKeys->purchased_date = (key_exists('purchased_date', $data)) ? $data['purchased_date']: $productLicenseKeys->purchased_date;
        $productLicenseKeys->status = (key_exists('status', $data)) ? $data['status']: $productLicenseKeys->status;
        $productLicenseKeys->wp_order_item_id = (key_exists('wp_order_item_id', $data)) ? $data['wp_order_item_id']: $productLicenseKeys->wp_order_item_id;
        $productLicenseKeys->customer_id = (key_exists('customer_id', $data)) ? $data['customer_id']: $productLicenseKeys->customer_id;
        $productLicenseKeys->order_id = (key_exists('order_id', $data)) ? $data['order_id']: $productLicenseKeys->order_id;
        $productLicenseKeys->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: $productLicenseKeys->updated_by;
        
        if ($productLicenseKeys->save())
        {
            $productLicenseKeys->delete();
            return $productLicenseKeys;
        }
        else
        {
            return false;
        } 
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id')->withTrashed();
    }

    public function package()
    {
        return $this->hasOne(Package::class, 'id', 'package_id');
    }

    public function licenseProduct()
    {
        return $this->hasOne(LicenseProduct::class, 'id', 'license_type_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function getHashedLicenseKeyAttribute() {
        return LicenseKeyHelper::licenseKeyHash($this->license_key);
    }
}
