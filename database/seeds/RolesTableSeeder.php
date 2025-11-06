<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use Carbon\Carbon;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [ 
            [
                'role_uuid' => Uuid::generate(4),
                'role_name' => 'Super Admin',
                'role_code' => 'SUPER_ADMIN',
                'role_description' => '',
                'status' => 'ACTIVE',
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'role_uuid' => Uuid::generate(4),
                'role_name' => 'Admin',
                'role_code' => 'ADMIN',
                'role_description' => '',
                'status' => 'ACTIVE',
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'role_uuid' => Uuid::generate(4),
                'role_name' => 'User',
                'role_code' => 'USER',
                'role_description' => '',
                'status' => 'ACTIVE',
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
        ];

        foreach ($roles as $role)
        {
            $existRole = Role::where('role_code', $role['role_code'])->first();

            if (empty($existRole))
            {
                Role::insertRecord($role);
            } 
        }
    }
}
