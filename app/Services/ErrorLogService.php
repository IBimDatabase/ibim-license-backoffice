<?php

namespace App\Services;

use App\Models\ErrorLog;
//use Webpatser\Uuid\Uuid;
use Illuminate\Support\Str;
//use GoldSpecDigital\LaravelEloquentUUID\Foundation\Uuid; // Added_by_Abdul_Rehman_for_Upgrade Laravel
use Illuminate\Support\Facades\DB;
DB::enableQueryLog();

class ErrorLogService
{
    public static function saveErrorLog($data)
    {
        $insertData = [
            //'error_uuid' => Uuid::generate(4),
            //'error_uuid' => Uuid::uuid4()->toString(),
            'error_uuid' => (string) Str::uuid(),
            //'error_uuid' => Str::uuid()->toString(),
            'error_code' => @$data['error_code'],
            'error_type' => @$data['error_type'],
            'route' => @$data['route'],
            'file_name' => @$data['file_name'],
            'error_message' => @$data['error_message'],
        ];

        if (auth()->user() !== null)
        {
            $insertData['created_by'] = auth()->user()->id;
        }
        
        ErrorLog::insertRecord($insertData);
    }
}
