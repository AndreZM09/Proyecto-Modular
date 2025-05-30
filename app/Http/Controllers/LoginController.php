<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Muestra el formulario de login
    public function showLoginForm()
    {
        return view('login');
    }

    // Procesa el intento de login
    public function login(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Intentar autenticar al usuario
        if (Auth::attempt($request->only('email', 'password'))) {
            // Si las credenciales son correctas, redirigir a la ruta 'campañas'
            return response()->json([
                'success'  => true,
                'message'  => 'Inicio de sesión exitoso.',
                'redirect' => route('campañas'),
            ]);
        }

        // Si la autenticación falla
        return response()->json([
            'success' => false,
            'message' => 'Credenciales incorrectas.',
        ]);
    }
}
