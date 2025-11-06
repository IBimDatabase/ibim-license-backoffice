<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;
    
    protected $table = 'order_items';
    protected $fillable = [
        'order_id', 'order_item_uuid', 'entity_type', 'entity_ref_id', 'license_type_id', 'quantity', 'unit_price', 'total_price', 'additional_info', 'status', 'created_by', 'updated_by'
    ]; 

    public static function insertRecord($data)
    {
        $orderItem = new OrderItem();

        $orderItem->order_id = (key_exists('order_id', $data)) ? $data['order_id']: NULL;
        $orderItem->order_item_uuid = (key_exists('order_item_uuid', $data)) ? $data['order_item_uuid']: NULL;
        $orderItem->entity_type = (key_exists('entity_type', $data)) ? $data['entity_type']: NULL;
        $orderItem->entity_ref_id = (key_exists('entity_ref_id', $data)) ? $data['entity_ref_id']: NULL;
        $orderItem->license_type_id = (key_exists('license_type_id', $data)) ? $data['license_type_id']: NULL;
        $orderItem->quantity = (key_exists('quantity', $data)) ? $data['quantity']: NULL;
        $orderItem->unit_price = (key_exists('unit_price', $data)) ? $data['unit_price']: NULL;
        $orderItem->total_price = (key_exists('total_price', $data)) ? $data['total_price']: NULL;
        $orderItem->additional_info = (key_exists('additional_info', $data)) ? $data['additional_info']: NULL;
        $orderItem->status = (key_exists('status', $data)) ? $data['status']: NULL;
        $orderItem->created_by = (key_exists('created_by', $data)) ? $data['created_by']: NULL;
        $orderItem->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: NULL;

        if ($orderItem->save())
            return $orderItem->where('id', $orderItem->id)->first();
        else   
            return false;
    }

    public static function updateRecord($data, $id)
    {
        $orderItem = OrderItem::find($id);

        $orderItem->order_id = (key_exists('order_id', $data)) ? $data['order_id']: $orderItem->order_id;
        $orderItem->entity_type = (key_exists('entity_type', $data)) ? $data['entity_type']: $orderItem->entity_type;
        $orderItem->entity_ref_id = (key_exists('entity_ref_id', $data)) ? $data['entity_ref_id']: $orderItem->entity_ref_id;
        $orderItem->license_type_id = (key_exists('license_type_id', $data)) ? $data['license_type_id']: $orderItem->license_type_id;
        $orderItem->quantity = (key_exists('quantity', $data)) ? $data['quantity']: $orderItem->quantity;
        $orderItem->unit_price = (key_exists('unit_price', $data)) ? $data['unit_price']: $orderItem->unit_price;
        $orderItem->total_price = (key_exists('total_price', $data)) ? $data['total_price']: $orderItem->total_price;
        $orderItem->additional_info = (key_exists('additional_info', $data)) ? $data['additional_info']: $orderItem->additional_info;
        $orderItem->status = (key_exists('status', $data)) ? $data['status']: $orderItem->status;
        $orderItem->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: $orderItem->updated_by;

        if ($orderItem->save())
            return $orderItem->where('id', $orderItem->id)->first();
        else   
            return false;
    }

    public function licenseType()
    {
        return $this->hasOne(LicenseType::class, 'id', 'license_type_id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'entity_ref_id')->withTrashed();
    }
}
