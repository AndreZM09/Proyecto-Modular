<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\EstadisticasController;

// Ruta para mostrar el formulario de login
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');

// Ruta para procesar el login vía AJAX
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Ruta de estadísticas, protegida por el middleware 'auth'

Route::get('/estadisticas', [EstadisticasController::class, 'index'])->name('estadisticas');

Route::post('/estadisticas/upload-image', [EstadisticasController::class, 'uploadImage'])->name('estadisticas.upload-image');
Route::get('/estadisticas/current-image', [EstadisticasController::class, 'getCurrentImage'])->name('estadisticas.current-image');

Route::get('/track-click', function (Request $request) {
    // Registrar el clic en la base de datos
    DB::table('clicks')->insert([
        'email' => $request->query('email', 'desconocido'), // Captura el email del usuario si se pasa como parámetro
        'created_at' => now(),
    ]);

    // Redirigir al destino final (Google en este caso)
    return Redirect::to('https://www.google.com');
});