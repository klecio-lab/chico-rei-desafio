<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuário demo do README/ambiente (senha plain: o cast hashed do model cuida).
        User::firstOrCreate(
            ['email' => 'demo@chicorei.test'],
            ['name' => 'Demo', 'password' => 'password'],
        );
    }
}
