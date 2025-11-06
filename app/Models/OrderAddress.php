<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderAddress extends Model
{
    use SoftDeletes;
    
    protected $table = 'order_addresses';
    protected $fillable = [
        'order_id', 'address_type', 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country', 'email', 'phone', 'created_by', 'updated_by'
    ]; 

    public static function insertRecord($data)
    {
        $orderAddress = new OrderAddress();

        $orderAddress->order_id = (key_exists('order_id', $data)) ? $data['order_id']: NULL;
        $orderAddress->address_type = (key_exists('address_type', $data)) ? $data['address_type']: NULL;
        $orderAddress->first_name = (key_exists('first_name', $data)) ? $data['first_name']: NULL;
        $orderAddress->last_name = (key_exists('last_name', $data)) ? $data['last_name']: NULL;
        $orderAddress->company = (key_exists('company', $data)) ? $data['company']: NULL;
        $orderAddress->address_1 = (key_exists('address_1', $data)) ? $data['address_1']: NULL;
        $orderAddress->address_2 = (key_exists('address_2', $data)) ? $data['address_2']: NULL;
        $orderAddress->city = (key_exists('city', $data)) ? $data['city']: NULL;
        $orderAddress->state = (key_exists('state', $data)) ? $data['state']: NULL;
        $orderAddress->postcode = (key_exists('postcode', $data)) ? $data['postcode']: NULL;
        $orderAddress->country = (key_exists('country', $data)) ? $data['country']: NULL;
        $orderAddress->email = (key_exists('email', $data)) ? $data['email']: NULL;
        $orderAddress->phone = (key_exists('phone', $data)) ? $data['phone']: NULL;
        $orderAddress->created_by = (key_exists('created_by', $data)) ? $data['created_by']: NULL;
        $orderAddress->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: NULL;

        if ($orderAddress->save())
            return $orderAddress->where('id', $orderAddress->id)->first();
        else   
            return false;
    }

    public static function updateRecord($data, $id)
    {
        $orderAddress = OrderAddress::find($id);

        $orderAddress->order_id = (key_exists('order_id', $data)) ? $data['order_id']: $orderAddress->order_id;
        $orderAddress->address_type = (key_exists('address_type', $data)) ? $data['address_type']: $orderAddress->address_type;
        $orderAddress->first_name = (key_exists('first_name', $data)) ? $data['first_name']: $orderAddress->first_name;
        $orderAddress->last_name = (key_exists('last_name', $data)) ? $data['last_name']: $orderAddress->last_name;
        $orderAddress->company = (key_exists('company', $data)) ? $data['company']: $orderAddress->company;
        $orderAddress->address_1 = (key_exists('address_1', $data)) ? $data['address_1']: $orderAddress->address_1;
        $orderAddress->address_2 = (key_exists('address_2', $data)) ? $data['address_2']: $orderAddress->address_2;
        $orderAddress->city = (key_exists('city', $data)) ? $data['city']: $orderAddress->city;
        $orderAddress->state = (key_exists('state', $data)) ? $data['state']: $orderAddress->state;
        $orderAddress->postcode = (key_exists('postcode', $data)) ? $data['postcode']: $orderAddress->postcode;
        $orderAddress->country = (key_exists('country', $data)) ? $data['country']: $orderAddress->country;
        $orderAddress->email = (key_exists('email', $data)) ? $data['email']: $orderAddress->email;
        $orderAddress->phone = (key_exists('phone', $data)) ? $data['phone']: $orderAddress->phone;
        $orderAddress->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: $orderAddress->updated_by;


        if ($orderAddress->save())
            return $orderAddress;
        else   
            return false;
    }
}
