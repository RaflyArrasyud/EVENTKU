<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@eventku.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'organizer@eventku.test'],
            [
                'name' => 'Organizer Demo',
                'password' => Hash::make('password'),
                'role' => 'organizer',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'peserta@eventku.test'],
            [
                'name' => 'Peserta Demo',
                'password' => Hash::make('password'),
                'role' => 'peserta',
                'email_verified_at' => now(),
            ]
        );
    }
}