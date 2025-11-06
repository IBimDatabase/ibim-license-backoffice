<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $table = 'roles';
    protected $fillable = [
        'role_uuid', 'role_name', 'role_code', 'role_description', 'status', 'created_by', 'updated_by'
    ];

    public static function insertRecord($data)
    {
        $role = new Role();

        $role->role_uuid = (key_exists('role_uuid', $data)) ? $data['role_uuid']: NULL;
        $role->role_name = (key_exists('role_name', $data)) ? $data['role_name']: NULL;
        $role->role_code = (key_exists('role_code', $data)) ? $data['role_code']: NULL;
        $role->role_description = (key_exists('role_description', $data)) ? $data['role_description']: NULL;
        $role->status = (key_exists('status', $data)) ? $data['status']: NULL;

        if ($role->save())
            return $role;
        else   
            return false;
    }

    public static function updateRecord($data, $id)
    {
        $role = Role::where('role_uuid', $id)->first();

        $role->role_name = (key_exists('role_name', $data)) ? $data['role_name']: $role->role_name;
        $role->role_code = (key_exists('role_code', $data)) ? $data['role_code']: $role->role_code;
        $role->role_description = (key_exists('role_description', $data)) ? $data['role_description']: $role->role_description;
        $role->status = (key_exists('status', $data)) ? $data['status']: $role->status;

        if ($role->save())
            return $role;
        else   
            return false;
    }
}
