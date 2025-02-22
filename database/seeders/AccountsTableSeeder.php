<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account; // Importa el modelo Account
use Illuminate\Support\Facades\Hash; // Importa la clase Hash para encriptar la contraseña

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear un usuario de prueba
        Account::create([
            'email' => 'test@example.com',
            'pass' => 'password123', // Contraseña en texto plano (solo para pruebas)
            'pass_encrip' => Hash::make('password123'), // Contraseña encriptada
        ]);
    }
}