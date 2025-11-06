<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderPayment extends Model
{
    use SoftDeletes;
    
    protected $table = 'order_payments';
    protected $fillable = [
        'order_id', 'payment_uuid', 'payment_ref_no', 'payment_mode', 'transaction_type', 'transaction_ref_no', 'amount', 'service_charges', 'additional_info', 'payment_url', 'transaction_status', 'status', 'created_by', 'updated_by'
    ]; 

    public static function insertRecord($data)
    {
        $orderPayment = new OrderPayment();

        $orderPayment->order_id = (key_exists('order_id', $data)) ? $data['order_id']: NULL;
        $orderPayment->payment_uuid = (key_exists('payment_uuid', $data)) ? $data['payment_uuid']: NULL;
        $orderPayment->payment_ref_no = (key_exists('payment_ref_no', $data)) ? $data['payment_ref_no']: NULL;
        $orderPayment->payment_mode = (key_exists('payment_mode', $data)) ? $data['payment_mode']: NULL;
        $orderPayment->transaction_type = (key_exists('transaction_type', $data)) ? $data['transaction_type']: NULL;
        $orderPayment->transaction_ref_no = (key_exists('transaction_ref_no', $data)) ? $data['transaction_ref_no']: NULL;
        $orderPayment->amount = (key_exists('amount', $data)) ? $data['amount']: NULL;
        $orderPayment->service_charges = (key_exists('service_charges', $data)) ? $data['service_charges']: NULL;
        $orderPayment->additional_info = (key_exists('additional_info', $data)) ? $data['additional_info']: NULL;
        $orderPayment->payment_url = (key_exists('payment_url', $data)) ? $data['payment_url']: NULL;
        $orderPayment->transaction_status = (key_exists('transaction_status', $data)) ? $data['transaction_status']: NULL;
        $orderPayment->status = (key_exists('status', $data)) ? $data['status']: NULL;
        $orderPayment->created_by = (key_exists('created_by', $data)) ? $data['created_by']: NULL;
        $orderPayment->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: NULL;

        if ($orderPayment->save())
            return $orderPayment->where('id', $orderPayment->id)->first();
        else   
            return false;
    }

    public static function updateRecord($data, $id)
    {
        $orderPayment = OrderPayment::find($id);

        $orderPayment->order_id = (key_exists('order_id', $data)) ? $data['order_id']: $orderPayment->order_id;
        $orderPayment->payment_ref_no = (key_exists('payment_ref_no', $data)) ? $data['payment_ref_no']: $orderPayment->payment_ref_no;
        $orderPayment->payment_mode = (key_exists('payment_mode', $data)) ? $data['payment_mode']: $orderPayment->payment_mode;
        $orderPayment->transaction_type = (key_exists('transaction_type', $data)) ? $data['transaction_type']: $orderPayment->transaction_type;
        $orderPayment->transaction_ref_no = (key_exists('transaction_ref_no', $data)) ? $data['transaction_ref_no']: $orderPayment->transaction_ref_no;
        $orderPayment->amount = (key_exists('amount', $data)) ? $data['amount']: $orderPayment->amount;
        $orderPayment->service_charges = (key_exists('service_charges', $data)) ? $data['service_charges']: $orderPayment->service_charges;
        $orderPayment->additional_info = (key_exists('additional_info', $data)) ? $data['additional_info']: $orderPayment->additional_info;
        $orderPayment->payment_url = (key_exists('payment_url', $data)) ? $data['payment_url']: $orderPayment->payment_url;
        $orderPayment->transaction_status = (key_exists('transaction_status', $data)) ? $data['transaction_status']: $orderPayment->transaction_status;
        $orderPayment->status = (key_exists('status', $data)) ? $data['status']: $orderPayment->status;
        $orderPayment->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: $orderPayment->updated_by;

        if ($orderPayment->save())
            return $orderPayment->where('id', $orderPayment->id)->first();
        else   
            return false;
    }
}
