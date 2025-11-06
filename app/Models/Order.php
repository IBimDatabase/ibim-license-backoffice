<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    
    protected $table = 'orders';
    protected $fillable = [
        'order_uuid', 'wp_order_id', 'order_type', 'order_status', 'payment_status', 'tax', 'discount', 'total_price', 'customer_id', 'wp_order_json', 'status', 'order_source', 'order_reference_no', 'additional_info', 'order_placed_at', 'paid_at', 'cancelled_at', 'created_by', 'updated_by'
    ]; 

    public static function insertRecord($data)
    {
        $order = new Order();

        $order->order_uuid = (key_exists('order_uuid', $data)) ? $data['order_uuid']: NULL;
        $order->wp_order_id = (key_exists('wp_order_id', $data)) ? $data['wp_order_id']: NULL;
        $order->order_type = (key_exists('order_type', $data)) ? $data['order_type']: NULL;
        $order->order_status = (key_exists('order_status', $data)) ? $data['order_status']: NULL;
        $order->payment_status = (key_exists('payment_status', $data)) ? $data['payment_status']: NULL;
        $order->tax = (key_exists('tax', $data)) ? $data['tax']: NULL;
        $order->discount = (key_exists('discount', $data)) ? $data['discount']: NULL;
        $order->total_price = (key_exists('total_price', $data)) ? $data['total_price']: NULL;
        $order->customer_id = (key_exists('customer_id', $data)) ? $data['customer_id']: NULL;
        $order->wp_order_json = (key_exists('wp_order_json', $data)) ? $data['wp_order_json']: NULL;
        $order->status = (key_exists('status', $data)) ? $data['status']: 'ACTIVE';
        $order->order_source = (key_exists('order_source', $data)) ? $data['order_source']: NULL;
        $order->source = (key_exists('source', $data)) ? $data['source']: NULL;
        $order->order_reference_no = (key_exists('order_reference_no', $data)) ? $data['order_reference_no']: NULL;
        $order->additional_info = (key_exists('additional_info', $data)) ? $data['additional_info']: NULL;
        $order->order_placed_at = (key_exists('order_placed_at', $data)) ? $data['order_placed_at']: NULL;        
        $order->paid_at = (key_exists('paid_at', $data)) ? $data['paid_at']: NULL;
        $order->cancelled_at = (key_exists('cancelled_at', $data)) ? $data['cancelled_at']: NULL;
        $order->created_by = (key_exists('created_by', $data)) ? $data['created_by']: NULL;
        $order->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: NULL;

        if ($order->save())
            return $order->where('id', $order->id)->first();
        else   
            return false;
    }

    public static function updateRecord($data, $id)
    {
        $order = Order::find($id);

        $order->wp_order_id = (key_exists('wp_order_id', $data)) ? $data['wp_order_id']: $order->wp_order_id;
        $order->order_type = (key_exists('order_type', $data)) ? $data['order_type']: $order->order_type;
        $order->order_status = (key_exists('order_status', $data)) ? $data['order_status']: $order->order_status;
        $order->payment_status = (key_exists('payment_status', $data)) ? $data['payment_status']: $order->payment_status;
        $order->tax = (key_exists('tax', $data)) ? $data['tax']: $order->tax;
        $order->discount = (key_exists('discount', $data)) ? $data['discount']: $order->discount;
        $order->total_price = (key_exists('total_price', $data)) ? $data['total_price']: $order->total_price;
        $order->customer_id = (key_exists('customer_id', $data)) ? $data['customer_id']: $order->customer_id;
        $order->wp_order_json = (key_exists('wp_order_json', $data)) ? $data['wp_order_json']: $order->wp_order_json;
        $order->status = (key_exists('status', $data)) ? $data['status']: $order->status;
        $order->order_source = (key_exists('order_source', $data)) ? $data['order_source']: $order->order_source;
        $order->source = (key_exists('source', $data)) ? $data['source']: $order->source;
        $order->order_reference_no = (key_exists('order_reference_no', $data)) ? $data['order_reference_no']: $order->order_reference_no;
        $order->additional_info = (key_exists('additional_info', $data)) ? $data['additional_info']: $order->additional_info;
        $order->order_placed_at = (key_exists('order_placed_at', $data)) ? $data['order_placed_at']: $order->order_placed_at;
        $order->paid_at = (key_exists('paid_at', $data)) ? $data['paid_at']: $order->paid_at;
        $order->cancelled_at = (key_exists('cancelled_at', $data)) ? $data['cancelled_at']: $order->cancelled_at;
        $order->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: $order->updated_by;

        if ($order->save())
            return $order;
        else   
            return false;
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function license()
    {
        return $this->hasOne(ProductLicenseKeys::class, 'order_id', 'id')->withTrashed();
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id')->withTrashed();
    }
}
