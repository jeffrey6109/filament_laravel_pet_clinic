<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->role('admin')
            ->create([
            'phone' => '016-7864509',
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => password_hash('admin1234', PASSWORD_DEFAULT)
            ]);

        User::factory()->role('doctor')
            ->create([
                'phone' => '019-0956774',
                'name' => 'Doctor',
                'email' => 'doctor@doctor.com',
                'password' => password_hash('doctor1234', PASSWORD_DEFAULT)
            ]);

        User::factory()->role('doctor')
            ->create([
                'phone' => '016-0963566',
                'name' => 'Doctor2',
                'email' => 'doctor2@doctor.com',
                'password' => password_hash('doctor5678', PASSWORD_DEFAULT)
            ]);

        User::factory()->role('owner')
            ->create([
                'phone' => '010-6579805',
                'name' => 'Owner',
                'email' => 'owner@owner.com',
                'password' => password_hash('owner1234', PASSWORD_DEFAULT)
            ]);

        User::factory()->role('owner')
            ->create([
                'phone' => '011-0986457',
                'name' => 'Owner2',
                'email' => 'owner2@owner.com',
                'password' => password_hash('owner5678', PASSWORD_DEFAULT)
            ]);
    }
}
