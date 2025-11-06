<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErrorLog extends Model
{
    use SoftDeletes;

    protected $table = 'error_logs';
    protected $fillable = [
        'error_uuid', 'error_code', 'error_type', 'route', 'file_name', 'error_message', 'created_by', 'updated_by'
    ];

    public static function insertRecord($data)
    {
        $errorLog = new ErrorLog();

        $errorLog->error_uuid = (key_exists('error_uuid', $data)) ? $data['error_uuid']: NULL;
        $errorLog->error_code = (key_exists('error_code', $data)) ? $data['error_code']: NULL;
        $errorLog->error_type = (key_exists('error_type', $data)) ? $data['error_type']: NULL;
        $errorLog->route = (key_exists('route', $data)) ? $data['route']: NULL;
        $errorLog->file_name = (key_exists('file_name', $data)) ? $data['file_name']: NULL;
        $errorLog->error_message = (key_exists('error_message', $data)) ? $data['error_message']: NULL;
        $errorLog->created_by = (key_exists('created_by', $data)) ? $data['created_by']: NULL;

        if ($errorLog->save())
            return $errorLog->where('id', $errorLog->id)->first();
        else   
            return false;
    }
}
