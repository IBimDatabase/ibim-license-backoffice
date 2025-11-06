<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ProductsTableSeeder::class);
        $this->call(LicenseTypesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(UserRolesTableSeeder::class);
        $this->call(PackagesTableSeeder::class);
        $this->call(WPAttributeIdMapSeeder::class);
        $this->call(ProductCodeAssignSeeder::class);
        $this->call(OrdersSourceMappingSeeder::class);
    }
}
