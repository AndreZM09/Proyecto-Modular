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
        // Obtener datos básicos
        $clicksCount = Click::count();
        
        // Contar los correos enviados (registros únicos en la tabla clicks)
        $emailsSent = Click::distinct('email')->count('email');
        
        $opened = $clicksCount; // Consideramos que los clics son emails abiertos

        // Obtener la última imagen subida
        $currentImage = EmailImage::latest()->first();

        return view('estadisticas', compact(
            'clicksCount',
            'opened',
            'emailsSent',
            'currentImage'
        ));
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'subject' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);

        try {
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();
                
                // Guardar la imagen en el almacenamiento
                $path = $file->storeAs('email_images', $filename, 'public');
                
                // Crear registro en la base de datos
                $emailImage = EmailImage::create([
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'subject' => $request->input('subject', ''),
                    'description' => $request->input('description', '')
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Imagen guardada exitosamente',
                    'filename' => $filename
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la imagen: ' . $e->getMessage()
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
        $imageId = $request->query('img_id'); // Obtener el ID de la imagen

        // Obtener ubicación basada en IP
        $location = Location::get($ip);
        $municipio = 'Desconocido';

        if ($location) {
            $municipio = $this->determinarMunicipio($location->cityName);
        }

        // Primero verificamos si ya existe un registro para este email y esta imagen
        $existingClick = Click::where('email', $email)
                              ->where('id_img', $imageId)
                              ->first();

        if ($existingClick) {
            // Si ya existe, actualizamos la fecha del clic
            $existingClick->update(['created_at' => now()]);
            $idPerson = $existingClick->id_person;
        } else {
            // Determinar el siguiente id_person para esta imagen
            $maxIdPerson = Click::where('id_img', $imageId)->max('id_person') ?? 0;
            $idPerson = $maxIdPerson + 1;

            // Crear nuevo registro
            Click::create([
                'email' => $email,
                'ip_address' => $ip,
                'municipio' => $municipio,
                'id_img' => $imageId,
                'id_person' => $idPerson,
                'email_sent_at' => now() // Por defecto, consideramos que el correo se envió en este momento
            ]);
        }

        // Redirigir al destino final (Google en este caso)
        return redirect('https://www.google.com');
    }

    /**
     * Registra la apertura de un correo
     */
    public function trackOpen(Request $request)
    {
        $email = $request->query('email');
        $imageId = $request->query('img_id');

        // Verificar si ya existe un registro para este email y esta imagen
        $existingClick = Click::where('email', $email)
                              ->where('id_img', $imageId)
                              ->first();

        if (!$existingClick) {
            // Determinar el siguiente id_person para esta imagen
            $maxIdPerson = Click::where('id_img', $imageId)->max('id_person') ?? 0;
            $idPerson = $maxIdPerson + 1;

            // Crear registro solo con la apertura
            Click::create([
                'email' => $email,
                'id_img' => $imageId,
                'id_person' => $idPerson,
                'email_sent_at' => now()
            ]);
        }

        // Devolver una imagen transparente de 1x1 pixel
        $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($img)->header('Content-Type', 'image/gif');
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
            // Validación básica del email
            $request->validate([
                'email' => 'required|email',
                'subject' => 'nullable|string|max:255',
                'description' => 'nullable|string'
            ]);
            
            // Obtener el email y otros campos
            $email = $request->email;
            $subject = $request->input('subject', 'Correo importante');
            $description = $request->input('description', 'Información importante para ti.');
            
            // Verificar si hay imagen configurada
            $image = EmailImage::latest()->first();
            if (!$image) {
                \Log::error('No hay imágenes configuradas en la base de datos');
                return response()->json([
                    'success' => false,
                    'message' => 'No hay imagen configurada para enviar'
                ], 400);
            }
            
            // Verificar si la imagen existe en el disco
            $imagePath = storage_path('app/public/email_images/' . $image->filename);
            $publicPath = public_path('storage/email_images/' . $image->filename);
            
            \Log::info('Verificando rutas de imagen:', [
                'imagen_db' => $image->filename,
                'storage_path' => $imagePath,
                'public_path' => $publicPath,
                'storage_exists' => file_exists($imagePath),
                'public_exists' => file_exists($publicPath)
            ]);
            
            // Guardar el email en un archivo temporal
            $emailListPath = storage_path('app/email_list_temp.txt');
            file_put_contents($emailListPath, $email);
            
            // Configurar las variables para el script Python
            putenv("EMAIL_SUBJECT=" . $subject);
            putenv("EMAIL_DESCRIPTION=" . $description);
            putenv("EMAIL_LIST_PATH=" . $emailListPath);
            
            // Ejecutar el script Python
            $command = 'python ' . base_path() . '/resources/python/generar_emails.py 2>&1';
            exec($command, $output, $returnCode);
            
            \Log::info('Resultado del script Python:', [
                'return_code' => $returnCode,
                'output' => $output
            ]);
            
            // Eliminar el archivo temporal
            @unlink($emailListPath);
            
            if ($returnCode !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar el correo: ' . implode("\n", $output)
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Correo enviado exitosamente a ' . $email
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Excepción en sendTestEmail: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar la vista de correos.
     */
    public function showCorreos()
    {
        $currentImage = EmailImage::orderBy('created_at', 'desc')->first();
        return view('correos', compact('currentImage'));
    }

    /**
     * Mostrar la vista de estadísticas.
     */
    public function showEstadisticas()
    {
        // Obtener datos básicos
        $clicksCount = DB::table('clicks')->count();
        
        // Contar los correos enviados (registros únicos en la tabla clicks)
        $emailsSent = DB::table('clicks')->distinct()->count('email');
        
        $opened = $clicksCount; // Consideramos que los clics son emails abiertos

        // Obtener la última imagen subida
        $currentImage = EmailImage::latest()->first();
        
        // Obtener estadísticas por campaña (por imagen)
        $campaignStats = $this->getCampaignStats();

        return view('estadisticas', compact(
            'clicksCount',
            'opened',
            'emailsSent',
            'currentImage',
            'campaignStats'
        ));
    }

    /**
     * Muestra estadísticas de una campaña específica por su ID
     */
    public function showCampaignStats($id)
    {
        // Verificar que la campaña exista
        $campaign = EmailImage::findOrFail($id);
        
        // Obtener estadísticas de esta campaña específica
        $campaignStats = $this->getCampaignStats($id);
        
        // Si no hay estadísticas, crear un array vacío para evitar errores
        if (empty($campaignStats)) {
            $campaignStats = [];
        }
        
        // Contar correos enviados para esta campaña
        $emailsSent = Click::where('id_img', $id)->count();
        
        // Contar clics para esta campaña
        $clicksCount = Click::where('id_img', $id)
                          ->whereNotNull('ip_address')
                          ->count();
        
        // Abiertos = clics
        $opened = $clicksCount;
        
        // Título para la vista
        $campaignTitle = $campaign->subject ?: 'Campaña ' . $campaign->id;
        
        return view('estadisticas', compact(
            'clicksCount',
            'opened',
            'emailsSent',
            'campaign',
            'campaignStats',
            'campaignTitle'
        ));
    }

    /**
     * Obtiene estadísticas detalladas por campaña (imagen)
     * 
     * @param int|null $campaignId ID de la campaña específica (opcional)
     * @return array
     */
    private function getCampaignStats($campaignId = null)
    {
        // Obtener campañas - todas o una específica
        $query = EmailImage::query();
        
        if ($campaignId) {
            $query->where('id', $campaignId);
        }
        
        $campaigns = $query->get();
        $stats = [];
        
        foreach ($campaigns as $campaign) {
            // Contar correos enviados para esta campaña
            $sent = Click::where('id_img', $campaign->id)->count();
            
            // Si hay correos enviados, calcular estadísticas
            if ($sent > 0) {
                // Contar clics únicos para esta campaña (por email)
                $clicks = Click::where('id_img', $campaign->id)
                              ->whereNotNull('ip_address')
                              ->distinct('email')
                              ->count('email');
                
                $stats[] = [
                    'id' => $campaign->id,
                    'name' => $campaign->subject ?: 'Campaña ' . $campaign->id,
                    'image' => $campaign->filename,
                    'sent' => $sent,
                    'clicks' => $clicks,
                    'open_rate' => $sent > 0 ? round(($clicks / $sent) * 100, 1) : 0,
                    'date' => $campaign->created_at->format('d/m/Y H:i')
                ];
            }
        }
        
        return $stats;
    }

    /**
     * Procesa un archivo con lista de correos y envía emails
     */
    public function uploadEmailList(Request $request)
    {
        try {
            // Validación básica de la solicitud
            $request->validate([
                'emailFile' => 'required|file|max:10240', // 10MB máximo
                'subject' => 'nullable|string|max:255',
                'description' => 'nullable|string'
            ]);

            // Obtener los valores básicos
            $subject = $request->input('subject', 'Correo importante');
            $description = $request->input('description', 'Información importante para ti.');
            
            // Verificar si hay imagen configurada
            $image = EmailImage::latest()->first();
            if (!$image) {
                \Log::error('No hay imágenes configuradas en la base de datos para el envío masivo');
                return response()->json([
                    'success' => false,
                    'message' => 'No hay imagen configurada para enviar'
                ], 400);
            }
            
            // Verificar si la imagen existe en el disco
            $imagePath = storage_path('app/public/email_images/' . $image->filename);
            $publicPath = public_path('storage/email_images/' . $image->filename);
            
            \Log::info('Verificando rutas de imagen para envío masivo:', [
                'imagen_db' => $image->filename,
                'storage_path' => $imagePath,
                'public_path' => $publicPath,
                'storage_exists' => file_exists($imagePath),
                'public_exists' => file_exists($publicPath)
            ]);
            
            // Obtener el archivo subido
            $file = $request->file('emailFile');
            $filePath = $file->path();
            
            // Leer el contenido del archivo como texto plano
            $content = file_get_contents($filePath);
            
            // Extraer emails del contenido
            $validEmails = [];
            
            if ($content !== false) {
                // Buscar correos electrónicos mediante expresión regular
                preg_match_all('/[\w\.-_]+@[\w\.-]+\.[a-zA-Z]{2,}/', $content, $matches);
                
                if (!empty($matches[0])) {
                    foreach ($matches[0] as $email) {
                        $email = trim($email);
                        if (filter_var($email, FILTER_VALIDATE_EMAIL) && !in_array($email, $validEmails)) {
                            $validEmails[] = $email;
                        }
                    }
                }
            }
            
            // Verificar si se encontraron emails
            if (empty($validEmails)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron direcciones de correo válidas en el archivo'
                ], 400);
            }
            
            // Guardar los correos en un archivo temporal
            $emailListPath = storage_path('app/email_list_bulk_' . time() . '.txt');
            file_put_contents($emailListPath, implode("\n", $validEmails));
            
            // Configurar las variables para el script Python
            putenv("EMAIL_SUBJECT=" . $subject);
            putenv("EMAIL_DESCRIPTION=" . $description);
            putenv("EMAIL_LIST_PATH=" . $emailListPath);
            
            // Ejecutar el script Python
            $command = 'python ' . base_path() . '/resources/python/generar_emails.py 2>&1';
            exec($command, $output, $returnCode);
            
            \Log::info('Resultado del script Python para envío masivo:', [
                'return_code' => $returnCode,
                'output' => $output,
                'total_emails' => count($validEmails)
            ]);
            
            // Eliminar el archivo temporal
            @unlink($emailListPath);
            
            // Verificar el resultado de la ejecución
            if ($returnCode !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar los correos: ' . implode("\n", $output)
                ], 500);
            }
            
            // Registrar los envíos en la tabla clicks
            $now = now();
            
            // Determinar el siguiente id_person para esta imagen
            $maxIdPerson = Click::where('id_img', $image->id)->max('id_person') ?? 0;
            
            foreach ($validEmails as $index => $email) {
                Click::create([
                    'email' => $email,
                    'id_img' => $image->id,
                    'id_person' => $maxIdPerson + $index + 1, // Incrementar id_person para cada email
                    'email_sent_at' => $now
                ]);
            }
            
            // Respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Correos enviados exitosamente',
                'total' => count($validEmails)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Excepción en uploadEmailList: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }
}