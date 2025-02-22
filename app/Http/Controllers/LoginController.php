<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Importa la clase Hash
use App\Models\Account; // Importa el modelo Account

class LoginController extends Controller
{
    // Muestra el formulario de login
    public function showLoginForm()
    {
        return view('login');
    }

    // Procesa el login
    public function login(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Buscar el usuario por correo electrónico
        $user = Account::where('email', $request->email)->first();

        // Verificar si el usuario existe y si la contraseña es correcta
        if ($user && Hash::check($request->password, $user->pass_encrip)) {
            // Inicio de sesión exitoso
            return response()->json([
                'success' => true,
                'message' => 'Inicio de sesión exitoso.',
                'redirect' => route('estadisticas'), // URL de redirección
            ]);
        } else {
            // Credenciales incorrectas
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas.',
            ]);
        }
    }
}