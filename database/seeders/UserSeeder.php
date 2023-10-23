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
        $doctorRole = Role::whereName('doctor')->first();
        $ownerRole = Role::whereName('owner')->first();

        User::factory()->for($adminRole)
            ->create([
            'phone' => '016-7864509',
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => password_hash('admin1234', PASSWORD_DEFAULT)
            ]);

        User::factory()->for($doctorRole)
            ->create([
                'phone' => '019-0956774',
                'name' => 'Doctor',
                'email' => 'doctor@doctor.com',
                'password' => password_hash('doctor1234', PASSWORD_DEFAULT)
            ]);

        User::factory()->for($ownerRole)
            ->create([
                'phone' => '011-6579805',
                'name' => 'Owner',
                'email' => 'owner@owner.com',
                'password' => password_hash('owner1234', PASSWORD_DEFAULT)
            ]);
    }
}
