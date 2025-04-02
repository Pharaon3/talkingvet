<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'id' => 1,
            'username' => env('ADMIN_USERNAME'),
            'password' => Hash::make(env('ADMIN_PASSWORD')),
            'firstname' => env('ADMIN_FIRSTNAME'),
            'lastname' => env('ADMIN_LASTNAME'),
            'organization_id' => 1,
            'login_server' => 2
        ]);
    }
}
