<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'name' => 'Administrator',
            'email' => 'admin@technolife.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
    }
}
