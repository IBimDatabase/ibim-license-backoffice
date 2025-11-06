<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use Webpatser\Uuid\Uuid;
use Illuminate\Support\Facades\DB;
DB::enableQueryLog();

class UsersService
{
    public static function getUsersData($data)
    {
        $query = new User;

        if (!empty($data['name']))
            $query = $query->where(function($q) use ($data) {
                $q->where('first_name', 'like', '%'. $data['name'] .'%')
                ->orWhere('last_name', 'like', '%'. $data['name'] .'%');
            });

        if (!empty($data['user_name']))
            $query = $query->where('user_name', 'like', '%'. $data['user_name'] .'%');

        if (!empty($data['user_type']))
            $query = $query->where('user_type', 'like', $data['user_type']);

        if (!empty($data['email']))
            $query = $query->where('email', 'like', '%'. $data['email'] .'%');

        if (!empty($data['phone']))
            $query = $query->where('phone', 'like', '%'. $data['phone'] .'%');
        
        if (!empty($data['status']))
            $query = $query->where('status', 'like', $data['status']);

            $query = $query->orderBy((@$data['sort_by']) ? @$data['sort_by'] : 'created_at', (@$data['sort_order']) ? @$data['sort_order'] : 'DESC');
       
        if (!key_exists('page', $data) || $data['page'] == 'all')
            $users = $query->paginate(1000);
        else
            $users = $query->paginate(10);

        //dd (DB::getQueryLog());
        return json_encode(["status" => true, "code" => 200, "message" => 'Users Retrieved Successfully', "data" => $users, "status_code" => 200]);
    }

    public static function addUserData($data)
    {
        $userData = [
            'user_uuid' => Uuid::generate(4),
            'first_name' => key_exists('first_name', $data) ? $data['first_name'] : '',
            'last_name' => key_exists('last_name', $data) ? $data['last_name'] : '',
            'user_name' => key_exists('user_name', $data) ? $data['user_name'] : '',
            'user_type' => key_exists('user_type', $data) ? $data['user_type'] : '',
            'email' => key_exists('email', $data) ? $data['email'] : '',
            'phone' => key_exists('phone', $data) ? $data['phone'] : '',
            'password' => key_exists('password', $data) ? $data['password'] : '',
            'status' => key_exists('status', $data) ? $data['status'] : '',
            'created_by' => auth()->user()->id
        ];

        $user = User::insertRecord($userData);
        $user = User::find($user->id);
        $user->makeHidden(['id']);

        $role = Role::where('role_code', $user['user_type'])->first();

        $userRoleData = [
            'user_id' => $user->id,
            'role_id' => $role->id,
            'status' => 'ACTIVE',
            'created_by' => auth()->user()->id
        ];
        $UserRole = UserRole::insertRecord($userRoleData);
        
        if ($user)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "User Added Successfully", "data" => $user, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500]);
        }
    }


    public static function updateUserData($data)
    {
        $checkUserNameDuplication = User::where('user_uuid', '!=', $data['id'])->where('user_name', $data['user_name'])->first();

        if (!empty($checkUserNameDuplication))
        {
            $error = ["error" => ["The user name has already been taken."]];
            return json_encode(["status" => false, "code" => 422, "message" => "Incorrect Request Data", "data" => $error, "status_code" => 422]);
        }

        $userData = [
            'first_name' => key_exists('first_name', $data) ? $data['first_name'] : '',
            'last_name' => key_exists('last_name', $data) ? $data['last_name'] : '',
            'user_name' => key_exists('user_name', $data) ? $data['user_name'] : '',
            'user_type' => key_exists('user_type', $data) ? $data['user_type'] : '',
            'email' => key_exists('email', $data) ? $data['email'] : '',
            'phone' => key_exists('phone', $data) ? $data['phone'] : '',
            'status' => key_exists('status', $data) ? $data['status'] : '',
            'updated_by' => auth()->user()->id
        ];
        
        $user = User::updateRecord($userData, $data['id']);
        $user->makeHidden(['id']);

        if ($user)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "User Updated Successfully", "data" => $user, "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 500, "message" => "Something Went Wrong", "data" => "", "status_code" => 500]);
        }
        
    }

}
