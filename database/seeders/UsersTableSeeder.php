<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;  // <-- Usa el modelo User
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder // <-- Rename si quieres
{
    public function run(): void
    {
        User::create([
            'name' => 'Test User',            // O el nombre que prefieras
            'email' => 'test@example.com',    // Debe ser único
            'password' => bcrypt('password123'), // Password encriptada
        ]);
    }
}
