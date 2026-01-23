<?php

namespace App\Services;

use App\Models\Role;
//use Webpatser\Uuid\Uuid;
//use GoldSpecDigital\LaravelUuid\Uuid; // Added_by_Abdul_Rehman_for_Upgrade Laravel
use Illuminate\Support\Facades\DB;
DB::enableQueryLog();

class RolesService
{
    public static function getRolesData($data)
    {
        $query = new Role;

        if (!empty($data['role_name']))
            $query = $query->where('role_name', 'like', '%'. $data['role_name'] .'%');

        if (!empty($data['role_code']))
            $query = $query->where('role_code', 'like', '%'. $data['role_code'] .'%');

        if (!empty($data['role_description']))
            $query = $query->where('role_description', 'like', '%'. $data['role_description'] .'%');
        
        if (!empty($data['status']))
            $query = $query->where('status', 'like', $data['status']);

        $query = $query->orderBy('created_at', 'DESC');
       
        if (!key_exists('page', $data) || $data['page'] == 'all')
            $roles = $query->paginate(1000);
        else
            $roles = $query->paginate(10);

        //dd (DB::getQueryLog());
        return json_encode(["status" => true, "code" => 200, "message" => 'Roles Retrieved Successfully', "data" => $roles, "status_code" => 200]);
    }

    public static function addRoleData($data)
    {
        $roleData = [
            'role_uuid' => Uuid::generate(4),
            'role_name' => key_exists('role_name', $data) ? $data['role_name'] : '',
            'role_code' => key_exists('role_code', $data) ? $data['role_code'] : '',
            'role_description' => key_exists('role_description', $data) ? $data['role_description'] : '',
            'status' => key_exists('status', $data) ? $data['status'] : '',
            'created_by' => auth()->user()->id,
        ];

        $role = Role::insertRecord($roleData);
        $role = Role::find($role->id);
        $role->makeHidden(['id']);

        if ($role)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "Role Added Successfully", "data" => $role, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500]);
        }
    }

    public static function updateRoleData($data)
    {
        $checkDuplication = Role::where('role_uuid', '!=', $data['id'])->where(function($q) use ($data) {
            $q->where('role_name', $data['role_name']);
            $q->orWhere('role_code', $data['role_code']);
        })->first();

        if (!empty($checkDuplication))
        {
            $error = ["error" => ["The role name or role code has already been taken."]];
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error, "status_code" => 422]);
        }

        $roleData = [
            'role_name' => key_exists('role_name', $data) ? $data['role_name'] : '',
            'role_code' => key_exists('role_code', $data) ? $data['role_code'] : '',
            'role_description' => key_exists('role_description', $data) ? $data['role_description'] : '',
            'status' => key_exists('status', $data) ? $data['status'] : '',
            'updated_by' => auth()->user()->id,
        ];

        $role = Role::updateRecord($roleData, $data['id']);
        $role->makeHidden(['id']);

        if ($role)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "Role Updated Successfully", "data" => $role, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500]);
        }
        
    }

}
