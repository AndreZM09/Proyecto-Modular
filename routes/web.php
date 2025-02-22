<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController; // Asegúrate de importar el controlador correcto

// Ruta para mostrar el formulario de login
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');

// Ruta para procesar el login
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Ruta de estadísticas
Route::get('/estadisticas', function () {
    return view('estadisticas'); // Asegúrate de que esta vista exista
})->name('estadisticas')->middleware('auth'); // Protege esta ruta con autenticación