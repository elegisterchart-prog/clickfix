<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DevUserSeeder extends Seeder
{
    public function run()
    {
        // Create a dev user if not exists
        $email = env('DEV_SEED_EMAIL', 'dev+local@example.com');
        $defaultPassword = env('DEV_SEED_PASSWORD', env('APP_DEFAULT_PASSWORD', 'password123'));

        if (User::where('email', $email)->exists()) {
            $this->command->info('Dev user already exists: '.$email);
            return;
        }

        $user = User::create([
            'name' => 'Dev Local',
            'email' => $email,
            'password' => $defaultPassword,
            'role' => 'admin'
        ]);

        $this->command->info('Created dev user: '.$email.' (password from DEV_SEED_PASSWORD / APP_DEFAULT_PASSWORD)');
    }
}
