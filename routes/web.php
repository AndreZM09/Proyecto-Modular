<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController; // Asegúrate de importar el controlador correcto

// Ruta para mostrar el formulario de login
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');

// Ruta para procesar el login
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Ruta del dashboard (protegida)
Route::get('/dashboard', function () {
    return view('dashboard'); // Asegúrate de que esta vista exista
})->name('dashboard')->middleware('auth'); // Protege esta ruta con autenticación