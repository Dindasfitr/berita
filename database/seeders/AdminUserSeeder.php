<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat admin default jika belum ada
        if (!User::where('email', 'admin@berita.com')->exists()) {
            User::create([
                'username' => 'admin',
                'name' => 'Administrator',
                'email' => 'admin@berita.com',
                'password' => Hash::make('admin123'), // Password yang di-hash
                'role' => 'admin',
                'membership' => 'premium', // Admin otomatis premium
            ]);
        }

        // Buat admin PBL jika belum ada
        if (!User::where('email', 'pbl@example.com')->exists()) {
            User::create([
                'username' => 'pbl_admin',
                'name' => 'PBL Admin',
                'email' => 'pbl@example.com',
                'password' => Hash::make('123'), // Password yang di-hash
                'role' => 'admin',
                'membership' => 'premium',
            ]);
        }
    }
}
