<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [ 
            [
                'user_uuid' => Uuid::generate(4),
                'user_type' => 'SUPER_ADMIN',
                'user_name' => 'superadmin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'superadmin@ibim.com',
                'phone' => '9999999999',
                'password' => 'Super2022',
                'status' => 'ACTIVE',
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'user_uuid' => Uuid::generate(4),
                'user_type' => 'ADMIN',
                'user_name' => 'admin',
                'first_name' => 'Admin',
                'last_name' => '',
                'email' => 'admin@ibim.com',
                'phone' => '9999999999',
                'password' => 'Admin2022',
                'status' => 'ACTIVE',
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'user_uuid' => Uuid::generate(4),
                'user_type' => 'USER',
                'user_name' => 'user',
                'first_name' => 'User',
                'last_name' => '',
                'email' => 'user@ibim.com',
                'phone' => '9999999999',
                'password' => 'User2022',
                'status' => 'ACTIVE',
                'created_at' => Carbon::now()->toDateTimeString(),
            ]
        ];

        foreach ($users as $user)
        {
            $existUser = User::where('user_name', $user['user_name'])->first();

            if (empty($existUser))
            {
                User::insertRecord($user);
            }
            else 
            {
                User::updateRecord($user, $existUser->user_uuid);
            }
        }
    }
}
