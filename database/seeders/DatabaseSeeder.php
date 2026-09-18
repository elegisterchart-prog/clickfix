<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $defaultPassword = env('APP_DEFAULT_PASSWORD', 'password123');

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => $defaultPassword,
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'technician@example.com'],
            [
                'name' => 'Technician User',
                'password' => $defaultPassword,
                'role' => 'technician',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Customer User',
                'password' => $defaultPassword,
                'role' => 'user',
            ]
        );
    }
}
