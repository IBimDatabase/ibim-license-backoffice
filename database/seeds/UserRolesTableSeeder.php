<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\UserRole;
use Carbon\Carbon;

class UserRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userRoles = [ 
            [
                'user_id' => '1',
                'role_id' => '1',
                'status' => 'ACTIVE',
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'user_id' => '2',
                'role_id' => '2',
                'status' => 'ACTIVE',
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'user_id' => '3',
                'role_id' => '3',
                'status' => 'ACTIVE',
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
        ];

        foreach ($userRoles as $userRole)
        {
            $existRole = UserRole::where([
                'user_id' => $userRole['user_id'],
                'role_id' => $userRole['role_id']
            ])->first();

            if (empty($existRole))
            {
                UserRole::insertRecord($userRole);
            } 
        }
    }
}
