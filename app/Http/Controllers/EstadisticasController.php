<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Click;
use App\Models\EmailImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Stevebauman\Location\Facades\Location;
use Illuminate\Support\Facades\Log;

class EstadisticasController extends Controller
{
    public function index()
    {
        $clicksCount = Click::count();
        $totalEmails = 100; // Cambiar por el número real de emails enviados
        
        $opened = $clicksCount;
        $notOpened = $totalEmails - $opened;

        // Obtener clics por municipio
        $clicksByMunicipio = Click::select('municipio', DB::raw('count(*) as total'))
                                ->groupBy('municipio')
                                ->orderBy('total', 'desc')
                                ->get();

        $totalClicsMunicipios = $clicksByMunicipio->sum('total');

        // Preparar colores para municipios
        $municipioColors = [];
        foreach ($clicksByMunicipio as $municipio) {
            $municipioColors[$municipio->municipio] = $this->getMunicipioColor($municipio->municipio);
        }

        // Obtener la última imagen subida
        $currentImage = EmailImage::latest()->first();

        return view('estadisticas', compact(
            'clicksCount',
            'opened',
            'notOpened',
            'totalEmails',
            'clicksByMunicipio',
            'totalClicsMunicipios',
            'municipioColors',
            'currentImage'
        ));
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();
                
                // Guardar la imagen en el almacenamiento
                $path = $file->storeAs('email_images', $filename, 'public');
                
                // Crear registro en la base de datos
                EmailImage::create([
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Imagen subida exitosamente',
                    'filename' => $filename
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCurrentImage()
    {
        $image = EmailImage::latest()->first();
        if ($image) {
            return response()->json([
                'success' => true,
                'filename' => $image->filename
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'No hay imagen configurada'
        ]);
    }

    public function trackClick(Request $request)
    {
        $email = $request->query('email');
        $ip = $request->ip();

        // Obtener ubicación basada en IP
        $location = Location::get($ip);
        $municipio = 'Desconocido';

        if ($location) {
            $municipio = $this->determinarMunicipio($location->cityName);
        }

        Click::create([
            'email' => $email,
            'ip_address' => $ip,
            'municipio' => $municipio
        ]);

        return response('Clic registrado', 200);
    }

    private function determinarMunicipio($cityName)
    {
        $municipios = [
            'Guadalajara',
            'Zapopan',
            'Tlaquepaque',
            'Tonalá',
            'Tlajomulco'
        ];

        foreach ($municipios as $municipio) {
            if (stripos($cityName, $municipio) !== false) {
                return $municipio;
            }
        }

        return 'Desconocido';
    }

    private function getMunicipioColor($municipio)
    {
        $colors = [
            'Guadalajara' => '#36a2eb',
            'Zapopan' => '#ff6384',
            'Tlaquepaque' => '#ffce56',
            'Tonalá' => '#4bc0c0',
            'Tlajomulco' => '#9966ff',
            'Desconocido' => '#c9cbcf'
        ];

        return $colors[$municipio] ?? $colors['Desconocido'];
    }

    public function sendTestEmail(Request $request)
    {
        try {
            // Obtener el email del destinatario desde el .env
            $recipient = env('EMAIL_RECIPIENT');
            
            if (!$recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se ha configurado un email de destinatario'
                ], 400);
            }

            // Cambiar al directorio del proyecto
            $projectPath = base_path();
            chdir($projectPath);

            // Ejecutar el script de Python con la ruta completa
            $command = 'python ' . $projectPath . '/resources/python/generar_emails.py 2>&1';
            exec($command, $output, $return_var);

            // Registrar la salida para depuración
            Log::info('Salida del comando Python:', [
                'output' => $output,
                'return_var' => $return_var,
                'command' => $command
            ]);

            if ($return_var !== 0) {
                Log::error('Error al enviar el correo de prueba', [
                    'output' => $output,
                    'return_var' => $return_var
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar el correo de prueba: ' . implode("\n", $output)
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Correo de prueba enviado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al enviar el correo de prueba: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo de prueba: ' . $e->getMessage()
            ], 500);
        }
    }
}