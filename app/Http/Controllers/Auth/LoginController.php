<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    // Muestra el formulario de login
    public function showLoginForm()
    {
        return view('login');
    }

    // Procesa el login (por ahora solo básico)
    public function login(Request $request)
    {
        // Aquí puedes agregar la validación de los datos de login

        // Por ahora solo redirigimos a la página principal
        return redirect('/');
    }
}
