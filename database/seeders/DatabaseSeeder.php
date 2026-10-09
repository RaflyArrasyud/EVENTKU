<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@eventku.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Organizer Demo',
            'email' => 'organizer@eventku.test',
            'password' => Hash::make('password'),
            'role' => 'organizer',
        ]);

        User::create([
            'name' => 'Peserta Demo',
            'email' => 'peserta@eventku.test',
            'password' => Hash::make('password'),
            'role' => 'peserta',
        ]);
    }
}