<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@unair.ac.id'],
            [
                'name'     => 'Admin Perpustakaan',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@unair.ac.id'],
            [
                'name'     => 'Staff Perpustakaan',
                'password' => Hash::make('user123'),
                'role'     => 'user',
            ]
        );
    }
}