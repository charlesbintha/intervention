<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin GUT',
            'email' => 'admin@gut.sn',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@gut.sn');
        $this->command->info('Password: admin123');
    }
}
