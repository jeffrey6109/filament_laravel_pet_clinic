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
        User::factory()
            ->create([
            'role_id' => Role::whereName('admin')->first()->id, // Foreign key seed
            'phone' => '55512347890',
            // 'name' => 'admin',
            // 'email' => 'admin@admin.com',
            // 'password' => password_hash('admin1234', PASSWORD_DEFAULT)
        ]);
    }
}
