<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class OrderEmailLog extends Model
{
    use SoftDeletes;

    protected $table = 'order_email_logs';
    protected $fillable = [
        'email_uuid', 'customer_id', 'entity_type', 'email_to', 'subject'
    ]; 

    public static function insertRecord($data)
    {
        $contactEmailLog = new OrderEmailLog();

        $contactEmailLog->email_uuid = (key_exists('email_uuid', $data)) ? $data['email_uuid']: NULL;
        $contactEmailLog->customer_id = (key_exists('customer_id', $data)) ? $data['customer_id']: NULL;
        $contactEmailLog->entity_type = (key_exists('entity_type', $data)) ? $data['entity_type']: NULL;
        $contactEmailLog->email_to = (key_exists('email_to', $data)) ? $data['email_to']: NULL;
        $contactEmailLog->subject = (key_exists('subject', $data)) ? $data['subject']: NULL;

        if ($contactEmailLog->save())
            return $contactEmailLog->where('id', $contactEmailLog->id)->first();
        else   
            return false;
    }

    public static function updateRecord($data, $id)
    {
        $contactEmailLog = OrderEmailLog::find($id);

        $contactEmailLog->customer_id = (key_exists('customer_id', $data)) ? $data['customer_id']: $contactEmailLog->customer_id;
        $contactEmailLog->entity_type = (key_exists('entity_type', $data)) ? $data['entity_type']: $contactEmailLog->entity_type;
        $contactEmailLog->email_to = (key_exists('email_to', $data)) ? $data['email_to']: $contactEmailLog->email_to;
        $contactEmailLog->subject = (key_exists('subject', $data)) ? $data['subject']: $contactEmailLog->subject;
        
        if ($contactEmailLog->save())
            return $contactEmailLog->where('id', $contactEmailLog->id)->first();
        else   
            return false;
    }
}
