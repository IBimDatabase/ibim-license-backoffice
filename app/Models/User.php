<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public static function insertRecord($data)
    {
        $user = new User();

        $user->user_uuid = (key_exists('user_uuid', $data)) ? $data['user_uuid']: NULL;
        $user->first_name = (key_exists('first_name', $data)) ? $data['first_name']: NULL;
        $user->last_name = (key_exists('last_name', $data)) ? $data['last_name']: NULL;
        $user->user_name = (key_exists('user_name', $data)) ? $data['user_name']: NULL;
        $user->user_type = (key_exists('user_type', $data)) ? $data['user_type']: NULL;
        $user->email = (key_exists('email', $data)) ? $data['email']: NULL;
        $user->phone = (key_exists('phone', $data)) ? $data['phone']: NULL;        
        $user->password = (key_exists('password', $data)) ? $data['password']: NULL;        
        $user->status = (key_exists('status', $data)) ? $data['status']: NULL;
        $user->created_by = (key_exists('created_by', $data)) ? $data['created_by']: NULL;

        if ($user->save())
            return $user;
        else   
            return false;
    }

    public static function updateRecord($data, $id)
    {
        $user = User::where('user_uuid', $id)->first();

        $user->first_name = (key_exists('first_name', $data)) ? $data['first_name']: $user->first_name;
        $user->last_name = (key_exists('last_name', $data)) ? $data['last_name']: $user->last_name;
        $user->user_name = (key_exists('user_name', $data)) ? $data['user_name']: $user->user_name;
        $user->user_type = (key_exists('user_type', $data)) ? $data['user_type']: $user->user_type;
        $user->email = (key_exists('email', $data)) ? $data['email']: $user->email;
        $user->phone = (key_exists('phone', $data)) ? $data['phone']: $user->phone;           
        $user->status = (key_exists('status', $data)) ? $data['status']: $user->status;
        $user->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: $user->updated_by;


        if ($user->save())
            return $user;
        else   
            return false;
    }

    public static function changePassword($data, $id)
    {
        $user = User::where('user_uuid', $id)->first();

        $user->password = (key_exists('password', $data)) ? $data['password']: $user->password; 
        $user->updated_by = (key_exists('updated_by', $data)) ? $data['updated_by']: $user->updated_by;

        if ($user->save())
            return $user;
        else   
            return false;
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function setFirstNameAttribute($firstName)
    {
        if (!empty($firstName))
        {
            $this->attributes['first_name'] = ucfirst($firstName);
        }
    }

    public function isSuperAdmin()
    {
        return auth()->user()->user_type === 'SUPER_ADMIN'; 
    }

    public function isAdmin()
    {
        return auth()->user()->user_type === 'ADMIN'; 
    }

    public function isUser()
    {
        return auth()->user()->user_type === 'USER'; 
    }
    
}
