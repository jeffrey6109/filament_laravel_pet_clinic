<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::whereName('admin')->first();

        User::factory()->for($adminRole)
            ->create([
            'phone' => '55512347890',
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => password_hash('admin1234', PASSWORD_DEFAULT)
        ]);
    }
}
