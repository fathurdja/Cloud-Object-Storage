<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1 Admin User
        User::updateOrCreate(
            ['email' => 'admin@office.local'],
            [
                'name' => 'Administrator Kantor',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // 2 Staff Users
        User::updateOrCreate(
            ['email' => 'staff1@office.local'],
            [
                'name' => 'Staff Administrasi 1',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff2@office.local'],
            [
                'name' => 'Staff Administrasi 2',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'is_active' => true,
            ]
        );
    }
}

