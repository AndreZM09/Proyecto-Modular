<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Intentar autenticar: esto buscará en la tabla "users", columna "password"
        // gracias a que en config/auth.php tienes el provider apuntando a App\Models\User
        if (Auth::attempt($request->only('email', 'password'))) {
            // Si las credenciales son correctas
            return response()->json([
                'success'  => true,
                'message'  => 'Inicio de sesión exitoso.',
                'redirect' => route('estadisticas'),
            ]);
        }

        // Si la autenticación falla
        return response()->json([
            'success' => false,
            'message' => 'Credenciales incorrectas.',
        ]);
    }
}
