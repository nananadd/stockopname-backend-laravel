<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        // Akun Manajer (Untuk lihat grafik & performa)
        User::create([
            'name' => 'Bapak Manajer',
            'email' => 'manager@sigmastationery.com',
            'password' => Hash::make('sigma123'), // Password default
            'role_id' => '3', // Role ID untuk Manajer = 3
        ]);

        // Akun Supervisor (Untuk ACC hasil hitungan)
        User::create([
            'name' => 'Ibu Supervisor',
            'email' => 'spv@sigmastationery.com',
            'password' => Hash::make('sigma123'),
            'role_id' => '4', // Role ID untuk Supervisor = 4
        ]);

        // Akun Supervisor (Untuk ACC hasil hitungan)
        User::create([
            'name' => 'Bapak Supervisor',
            'email' => 'spv1@sigmastationery.com',
            'password' => Hash::make('sigma123'),
            'role_id' => '4', // Role ID untuk Supervisor = 4
        ]);

        // Akun Staf Lapangan (Untuk login di HP / Flutter)
        User::create([
            'name' => 'Staf Gudang (Picker)',
            'email' => 'staff@sigmastationery.com',
            'password' => Hash::make('sigma123'),
            'role_id' => '5', // Role ID untuk Staf Lapangan = 5
        ]);

        // Akun Staf Lapangan (Untuk login di HP / Flutter)
        User::create([
            'name' => 'Staf Gudang 1',
            'email' => 'staff1@sigmastationery.com',
            'password' => Hash::make('sigma123'),
            'role_id' => '5', // Role ID untuk Staf Lapangan = 5
        ]);

        // Akun Staf Lapangan (Untuk login di HP / Flutter)
        User::create([
            'name' => 'Staf Gudang 2',
            'email' => 'staff2@sigmastationery.com',
            'password' => Hash::make('sigma123'),
            'role_id' => '5', // Role ID untuk Staf Lapangan = 5
        ]);

        // Akun Staf Lapangan (Untuk login di HP / Flutter)
        User::create([
            'name' => 'Staf Gudang 3',
            'email' => 'staff3@sigmastationery.com',
            'password' => Hash::make('sigma123'),
            'role_id' => '5', // Role ID untuk Staf Lapangan = 5
        ]);

        // Akun Staf Lapangan (Untuk login di HP / Flutter)
        User::create([
            'name' => 'Staf Gudang 4',
            'email' => 'staff4@sigmastationery.com',
            'password' => Hash::make('sigma123'),
            'role_id' => '5', // Role ID untuk Staf Lapangan = 5
        ]);

        // Akun Staf Lapangan (Untuk login di HP / Flutter)
        User::create([
            'name' => 'Staf Gudang 5',
            'email' => 'staff5@sigmastationery.com',
            'password' => Hash::make('sigma123'),
            'role_id' => '5', // Role ID untuk Staf Lapangan = 5
        ]);

        // Akun Staf Lapangan (Untuk login di HP / Flutter)
         User::create([
            'name' => 'Staf Gudang 6',
            'email' => 'staff6@sigmastationery.com',
            'password' => Hash::make('sigma123'),
            'role_id' => '5', // Role ID untuk Staf Lapangan = 5
        ]);

        // Akun Staf Lapangan (Untuk login di HP / Flutter)
        User::create([
            'name' => 'Staf Gudang 7',
            'email' => 'staff7@sigmastationery.com',
            'password' => Hash::make('sigma123'),
            'role_id' => '5', // Role ID untuk Staf Lapangan = 5
        ]);

        // Akun Staf Lapangan (Untuk login di HP / Flutter)
        User::create([
            'name' => 'Staf Gudang 8',
            'email' => 'staff8@sigmastationery.com',
            'password' => Hash::make('sigma123'),
            'role_id' => '5', // Role ID untuk Staf Lapangan = 5
        ]);

        // Akun Staf Lapangan (Untuk login di HP / Flutter)
        User::create([
            'name' => 'Staf Gudang 9',
            'email' => 'staff9  @sigmastationery.com',
            'password' => Hash::make('sigma123'),
            'role_id' => '5', // Role ID untuk Staf Lapangan = 5
        ]);
    }
}