<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\CampañasController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

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
Route::get('/estadisticas/campana/{id}', [EstadisticasController::class, 'showCampaignStats'])->name('estadisticas.campaign');

Route::post('/estadisticas/upload-image', [EstadisticasController::class, 'uploadImage'])->name('estadisticas.upload-image');
Route::get('/estadisticas/current-image', [EstadisticasController::class, 'getCurrentImage'])->name('estadisticas.current-image');

Route::post('/estadisticas/send-test-email', [EstadisticasController::class, 'sendTestEmail'])->name('estadisticas.send-test-email');
Route::post('/estadisticas/upload-email-list', [EstadisticasController::class, 'uploadEmailList'])->name('estadisticas.upload-email-list');

// Ruta para servir las imágenes de email directamente
Route::get('/campaign-images/{filename}', function ($filename) {
    $path = storage_path('app/public/email_images/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    $file = file_get_contents($path);
    $type = mime_content_type($path);
    
    return Response::make($file, 200, [
        'Content-Type' => $type,
        'Content-Disposition' => 'inline; filename="' . $filename . '"'
    ]);
})->name('campaign.image');

// Rutas de seguimiento
Route::get('/track-click', [EstadisticasController::class, 'trackClick']);
Route::get('/track-open', [EstadisticasController::class, 'trackOpen']);

// Ruta de prueba
Route::get('/test-click', [TestController::class, 'testClick']);