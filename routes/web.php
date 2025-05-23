<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\CampañasController;

// Ruta principal - redirige a correos
Route::get('/', function () {
    return redirect()->route('login');
});

//ruta de campañas (imagenes enviadas en bd)
Route::get('/campañas', [CampañasController::class, 'index'])->name('campañas');

// Ruta para mostrar el formulario de login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

// Ruta para procesar el login vía AJAX
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Rutas para las vistas separadas
Route::get('/correos', [EstadisticasController::class, 'showCorreos'])->name('correos');
Route::get('/estadisticas', [EstadisticasController::class, 'showEstadisticas'])->name('estadisticas.index');

Route::post('/estadisticas/upload-image', [EstadisticasController::class, 'uploadImage'])->name('estadisticas.upload-image');
Route::get('/estadisticas/current-image', [EstadisticasController::class, 'getCurrentImage'])->name('estadisticas.current-image');

Route::post('/estadisticas/send-test-email', [EstadisticasController::class, 'sendTestEmail'])->name('estadisticas.send-test-email');
Route::post('/estadisticas/upload-email-list', [EstadisticasController::class, 'uploadEmailList'])->name('estadisticas.upload-email-list');

Route::get('/track-click', function (Request $request) {
    // Registrar el clic en la base de datos
    $email = $request->query('email', 'desconocido');
    $ip = $request->ip();
    
    DB::table('clicks')->insert([
        'email' => $email,
        'ip_address' => $ip,
        'municipio' => 'Desconocido',
        'created_at' => now(),
    ]);

    // Redirigir al destino final (Google en este caso)
    return Redirect::to('https://www.google.com');
});