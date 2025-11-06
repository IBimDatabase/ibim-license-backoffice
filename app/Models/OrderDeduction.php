<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDeduction extends Model
{
    use SoftDeletes;
    
    protected $table = 'order_deductions';
    protected $fillable = [
        'deduction_uuid', 'order_id', 'order_item_id', 'deduction_type', 'deduction_ref_id', 'code', 'percentage', 'amount', 'additional_info', 'status', 'created_by', 'updated_by'
    ]; 

    public static function insertRecord($data)
    {
        $orderDeduction = new OrderDeduction();

        $orderDeduction->deduction_uuid = (key_exists('deduction_uuid', $data)) ? $data['deduction_uuid']: NULL;
        $orderDeduction->order_id = (key_exists('order_id', $data)) ? $data['order_id']: NULL;
        $orderDeduction->order_item_id = (key_exists('order_item_id', $data)) ? $data['order_item_id']: NULL;
        $orderDeduction->deduction_type = (key_exists('deduction_type', $data)) ? $data['deduction_type']: NULL;
        $orderDeduction->deduction_ref_id = (key_exists('deduction_ref_id', $data)) ? $data['deduction_ref_id']: NULL;
        $orderDeduction->code = (key_exists('code', $data)) ? $data['code']: NULL;
        $orderDeduction->percentage = (key_exists('percentage', $data)) ? $data['percentage']: NULL;
        $orderDeduction->amount = (key_exists('amount', $data)) ? $data['amount']: NULL;
        $orderDeduction->additional_info = (key_exists('additional_info', $data)) ? $data['additional_info']: NULL;
        $orderDeduction->status = (key_exists('status', $data)) ? $data['status']: NULL;
        $orderDeduction->created_by = (key_exists('created_by', $data)) ? $data['created_by']: NULL;
        $orderDeduction->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: NULL;

        if ($orderDeduction->save())
            return $orderDeduction->where('id', $orderDeduction->id)->first();
        else   
            return false;
    }

    public static function updateRecord($data, $id)
    {
        $orderDeduction = OrderDeduction::find($id);

        $orderDeduction->order_id = (key_exists('order_id', $data)) ? $data['order_id']: $orderDeduction->order_id;
        $orderDeduction->order_item_id = (key_exists('order_item_id', $data)) ? $data['order_item_id']: $orderDeduction->order_item_id;
        $orderDeduction->deduction_type = (key_exists('deduction_type', $data)) ? $data['deduction_type']: $orderDeduction->deduction_type;
        $orderDeduction->deduction_ref_id = (key_exists('deduction_ref_id', $data)) ? $data['deduction_ref_id']: $orderDeduction->deduction_ref_id;
        $orderDeduction->code = (key_exists('code', $data)) ? $data['code']: $orderDeduction->code;
        $orderDeduction->percentage = (key_exists('percentage', $data)) ? $data['percentage']: $orderDeduction->percentage;
        $orderDeduction->amount = (key_exists('amount', $data)) ? $data['amount']: $orderDeduction->amount;
        $orderDeduction->additional_info = (key_exists('additional_info', $data)) ? $data['additional_info']: $orderDeduction->additional_info;
        $orderDeduction->status = (key_exists('status', $data)) ? $data['status']: $orderDeduction->status;
        $orderDeduction->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: $orderDeduction->updated_by;

        if ($orderDeduction->save())
            return $orderDeduction;
        else   
            return false;
    }
}
