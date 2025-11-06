<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $table = 'customers';
    protected $fillable = [
        'customer_uuid', 'user_name', 'first_name', 'last_name', 'email', 'phone', 'status', 'created_by', 'updated_by'
    ];

    public static function insertRecord($data) 
    {
        $customer = new Customer();

        $customer->customer_uuid = (key_exists('customer_uuid', $data)) ? $data['customer_uuid']: NULL;
        $customer->user_name = (key_exists('user_name', $data)) ? $data['user_name']: NULL;
        $customer->first_name = (key_exists('first_name', $data)) ? $data['first_name']: NULL;
        $customer->last_name = (key_exists('last_name', $data)) ? $data['last_name']: NULL;
        $customer->email = (key_exists('email', $data)) ? $data['email']: NULL;
        $customer->phone = (key_exists('phone', $data)) ? $data['phone']: NULL;
        $customer->status = (key_exists('status', $data)) ? $data['status']: NULL;
        $customer->created_by = (key_exists('created_by', $data)) ? $data['created_by']: NULL;

        if ($customer->save())
            return $customer;
        else   
            return false;
    }

    public static function updateRecord($data, $id) 
    {
        $customer = Customer::where('id', $id)->first();
        if(!empty($customer)){
            $customer->user_name = (key_exists('user_name', $data)) ? $data['user_name']: $customer->user_name;
            $customer->first_name = (key_exists('first_name', $data)) ? $data['first_name']: $customer->first_name;
            $customer->last_name = (key_exists('last_name', $data)) ? $data['last_name']: $customer->last_name;
            $customer->email = (key_exists('email', $data)) ? $data['email']: $customer->email;
            $customer->phone = (key_exists('phone', $data)) ? $data['phone']: $customer->phone;
            $customer->status = (key_exists('status', $data)) ? $data['status']: $customer->status;
            $customer->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: $customer->updated_by;

            if ($customer->save())
                return $customer;
            else   
                return false;
        }
        
    }

}
