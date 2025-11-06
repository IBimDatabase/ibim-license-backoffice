<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRole extends Model
{
    use SoftDeletes;

    protected $table = 'user_roles';
    protected $fillable = [
        'user_id', 'role_id', 'status', 'created_by', 'updated_by'
    ];

    public static function insertRecord($data)
    {
        $user_role = new UserRole();

        $user_role->user_id = (key_exists('user_id', $data)) ? $data['user_id']: NULL;
        $user_role->role_id = (key_exists('role_id', $data)) ? $data['role_id']: NULL;
        $user_role->status = (key_exists('status', $data)) ? $data['status']: NULL;
        $user_role->created_by = (key_exists('created_by', $data)) ? $data['created_by']: NULL;

        if ($user_role->save())
            return $user_role;
        else   
            return false;
    }

    public static function updateRecord($data, $id)
    {
        $user_role = UserRole::find($id);

        $user_role->user_id = (key_exists('user_id', $data)) ? $data['user_id']: $user_role->user_id;
        $user_role->role_id = (key_exists('role_id', $data)) ? $data['role_id']: $user_role->role_id;
        $user_role->status = (key_exists('status', $data)) ? $data['status']: $user_role->status;
        $user_role->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: $user_role->updated_by;

        if ($user_role->save())
            return $user_role;
        else   
            return false;
    }
}
