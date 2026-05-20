<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Click;
use App\Models\EmailImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Stevebauman\Location\Facades\Location;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EstadisticasController extends Controller
{


    public function index()
    {
        // Obtener datos básicos
        $totalEmailsSent = Click::distinct('email')->count('email');
        $totalEmailsOpened = Click::whereNotNull('email_opened_at')->distinct('email')->count('email');
        $totalClicks = Click::whereNotNull('clicked_at')->distinct('email')->count('email');

        // Obtener la última imagen subida
        $currentImage = EmailImage::latest()->first();

        return view('estadisticas', compact(
            'totalEmailsSent',
            'totalEmailsOpened',
            'totalClicks',
            'currentImage'
        ));
    }

    public function uploadImage(Request $request)
    {
        try {
            // Validar la solicitud
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,bmp,tiff,svg',
                'subject' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'priority' => 'nullable|in:normal,high,urgent',
                'link_redirection' => 'nullable|url|max:2048'
            ]);

            // Procesar y guardar la imagen
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '_' . $image->getClientOriginalName();
                
                // Registrar información sobre el archivo
                \Log::info('Procesando archivo de imagen:', [
                    'original_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getMimeType(),
                    'size' => $image->getSize(),
                    'filename' => $filename
                ]);
                
                // Asegurarnos de que el directorio existe
                $storagePath = storage_path('app/public/email_images');
                if (!file_exists($storagePath)) {
                    mkdir($storagePath, 0755, true);
                }
                
                // Mover la imagen al almacenamiento
                $image->move($storagePath, $filename);
                
                // Verificar que el archivo se guardó correctamente
                $fullPath = $storagePath . '/' . $filename;
                \Log::info('Imagen guardada en storage:', [
                    'full_path' => $fullPath,
                    'exists' => file_exists($fullPath)
                ]);
                
                // Crear registro en la base de datos
                $emailImage = EmailImage::create([
                    'filename' => $filename,
                    'original_name' => $image->getClientOriginalName(),
                    'subject' => $request->input('subject'),
                    'description' => $request->input('description'),
                    'priority' => $request->input('priority', 'normal'),
                    'link_redirection' => $request->input('link_redirection')
                ]);
                
                \Log::info('Registro creado en la base de datos:', [
                    'id' => $emailImage->id,
                    'filename' => $emailImage->filename,
                    'subject' => $emailImage->subject
                ]);
                
                // Verificar que la imagen es accesible desde la web
                $publicPath = public_path('storage/email_images/' . $filename);
                \Log::info('Verificando accesibilidad web:', [
                    'public_path' => $publicPath,
                    'exists' => file_exists($publicPath),
                    'url' => asset('storage/email_images/' . $filename)
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Imagen subida exitosamente',
                    'data' => [
                        'id' => $emailImage->id,
                        'filename' => $emailImage->filename,
                        'url' => asset('storage/email_images/' . $filename)
                    ]
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ninguna imagen'
            ], 400);
            
        } catch (\Exception $e) {
            \Log::error('Error en uploadImage:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
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

    public function trackClick($id_img, $email)
    {
        // Log de entrada a la función trackClick
        \Log::info('trackClick: Iniciando seguimiento de clic.', [
            'id_img' => $id_img,
            'email' => $email,
            'raw_url' => request()->url(),
            'full_url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        $imageId = $id_img;
        $ip = request()->ip();
        $userAgent = request()->userAgent();
        $isMobile = $this->isMobileDevice($userAgent);

        \Log::info('trackClick: Parámetros recibidos', [
            'email' => $email,
            'imageId' => $imageId,
            'ip' => $ip,
            'is_mobile' => $isMobile,
            'user_agent' => $userAgent
        ]);

        $redirectUrl = 'https://www.google.com'; // URL por defecto
        $image = EmailImage::find($imageId);
        if ($image && $image->link_redirection) {
            $redirectUrl = $image->link_redirection;
            \Log::info('trackClick: Redirigiendo a URL personalizada.', ['url' => $redirectUrl]);
        }

        // Deshabilitar temporalmente la ubicación para evitar errores
        $municipio = 'Desconocido';
        
        // Comentado temporalmente para evitar errores
        // try {
        //     $location = Location::get($ip);
        //     if ($location) {
        //         $municipio = $this->determinarMunicipio($location->cityName);
        //     }
        // } catch (\Exception $e) {
        //     \Log::warning('Error obteniendo ubicación: ' . $e->getMessage());
        //     $municipio = 'Desconocido';
        // }

        // Primero verificamos si ya existe un registro para este email y esta imagen
        $existingClick = Click::where('email', $email)
                              ->where('id_img', $imageId)
                              ->first();

        if ($existingClick) {
            // Si ya existe, actualizamos SOLO el clic
            $existingClick->update([
                'clicked_at' => now()
            ]);
            \Log::info('trackClick: Registro de clic existente actualizado.', [
                'id' => $existingClick->id,
                'email' => $existingClick->email,
                'clicked_at' => $existingClick->clicked_at
            ]);
            $idPerson = $existingClick->id_person;
        } else {
            // Determinar el siguiente id_person para esta imagen
            $maxIdPerson = Click::where('id_img', $imageId)->max('id_person') ?? 0;
            $idPerson = $maxIdPerson + 1;

            // Crear nuevo registro
            $newClick = Click::create([
                'email' => $email,
                'ip_address' => $ip,
                'municipio' => $municipio,
                'id_img' => $imageId,
                'id_person' => $idPerson,
                'email_sent_at' => now(), // Por defecto, consideramos que el correo se envió en este momento
                'clicked_at' => now() // Registrar SOLO el momento del clic
            ]);
            \Log::info('trackClick: Nuevo registro de clic creado.', [
                'id' => $newClick->id,
                'email' => $newClick->email,
                'clicked_at' => $newClick->clicked_at
            ]);
        }

        // Estrategia de redirección según el dispositivo
        if ($isMobile) {
            \Log::info('trackClick: Dispositivo móvil detectado, usando redirección con JavaScript.');
            // Para dispositivos móviles, usar una página intermedia con JavaScript
            return view('mobile_redirect', [
                'redirectUrl' => $redirectUrl,
                'email' => $email,
                'imageId' => $imageId
            ]);
        } else {
            \Log::info('trackClick: Dispositivo de escritorio, usando redirección directa.');
            // Para dispositivos de escritorio, redirección directa
            return redirect($redirectUrl);
        }
    }

    /**
     * Detecta si el dispositivo es móvil basado en el User Agent
     */
    private function isMobileDevice($userAgent)
    {
        if (!$userAgent) return false;
        
        $mobileKeywords = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'Windows Phone', 
            'BlackBerry', 'Opera Mini', 'IEMobile', 'Mobile Safari',
            'CriOS', 'FxiOS', 'OPiOS', 'Vivaldi'
        ];
        
        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Registra la apertura de un correo mediante un pixel de seguimiento.
     *
     * Cambios respecto a la versión original:
     * - Decodifica el email por si viene URL-encoded (móviles usan más codificación).
     * - Registra el User-Agent y la IP para diferenciar aperturas reales de proxies.
     * - Detecta el proxy de Apple Mail Privacy Protection (MPP) y lo marca en el log.
     * - Devuelve headers anti-caché agresivos (Cache-Control, Pragma, Expires, Last-Modified)
     *   para evitar que Gmail/Apple/proxies sirvan el pixel desde caché y se pierdan aperturas.
     * - Añade Content-Length para mejor compatibilidad con clientes estrictos.
     */
    public function trackOpen($id_img, $email)
    {
        // Decodificar el email por si viene URL-encoded (ej. "user%40dominio.com")
        $email = urldecode($email);

        $userAgent = request()->userAgent();
        $ip = request()->ip();
        $isMobile = $this->isMobileDevice($userAgent);
        $isAppleProxy = $this->isAppleMailProxy($ip, $userAgent);

        \Log::info('trackOpen: Iniciando seguimiento de apertura.', [
            'id_img' => $id_img,
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'is_mobile' => $isMobile,
            'is_apple_proxy' => $isAppleProxy,
        ]);

        $imageId = $id_img;

        // Verificar si ya existe un registro para este email y esta imagen
        $existingClick = Click::where('email', $email)
                              ->where('id_img', $imageId)
                              ->first();

        if ($existingClick) {
            // Solo registramos la PRIMERA apertura real para no sobrescribirla con re-aperturas.
            // Si quieres registrar todas las aperturas, cambia la condición.
            if (!$existingClick->email_opened_at) {
                $existingClick->update([
                    'email_opened_at' => now(),
                    'ip_address' => $existingClick->ip_address ?: $ip,
                ]);
                \Log::info('trackOpen: Primera apertura registrada.', [
                    'id' => $existingClick->id,
                    'email' => $existingClick->email,
                    'email_opened_at' => $existingClick->email_opened_at,
                ]);
            } else {
                \Log::info('trackOpen: Re-apertura detectada (no se sobrescribe la primera).', [
                    'id' => $existingClick->id,
                    'email' => $existingClick->email,
                    'first_open' => $existingClick->email_opened_at,
                ]);
            }
        } else {
            // Determinar el siguiente id_person para esta imagen
            $maxIdPerson = Click::where('id_img', $imageId)->max('id_person') ?? 0;
            $idPerson = $maxIdPerson + 1;

            // Crear registro solo con la apertura
            $newClick = Click::create([
                'email' => $email,
                'ip_address' => $ip,
                'id_img' => $imageId,
                'id_person' => $idPerson,
                'email_sent_at' => now(),     // Se considera enviado en este momento si no hay registro previo
                'email_opened_at' => now(),
            ]);
            \Log::info('trackOpen: Nuevo registro de apertura creado.', [
                'id' => $newClick->id,
                'email' => $newClick->email,
                'email_opened_at' => $newClick->email_opened_at,
            ]);
        }

        // Pixel GIF transparente 1x1
        $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        // Headers anti-caché agresivos.
        // Sin estos, Gmail (que proxifica imágenes vía googleusercontent.com) y los clientes
        // móviles cachean el pixel: el correo puede abrirse N veces y solo registramos 1
        // (o ninguna si se sirve desde caché edge). Forzamos revalidación en cada apertura.
        return response($img, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Content-Length', (string) strlen($img))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT')
            ->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    /**
     * Detecta si la solicitud proviene del proxy de Apple Mail Privacy Protection.
     *
     * Desde iOS 15 / macOS Monterey, Apple precarga TODAS las imágenes de correos
     * desde su propio rango de IPs (17.0.0.0/8). Esto genera "aperturas" que en
     * realidad son precargas automáticas y no significan que el usuario abrió el correo.
     * Lo registramos en logs para poder filtrarlo en estadísticas si hace falta.
     */
    private function isAppleMailProxy($ip, $userAgent)
    {
        if (!$ip) return false;

        // El rango principal de Apple es 17.0.0.0/8.
        // (Apple publica los rangos completos; este es el más amplio y cubre MPP.)
        if ($this->ipInRange($ip, '17.0.0.0', 8)) {
            return true;
        }

        return false;
    }

    /**
     * Verifica si una IP (IPv4) está dentro de un rango CIDR dado.
     */
    private function ipInRange($ip, $subnet, $bits)
    {
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        if ($ipLong === false || $subnetLong === false) {
            return false;
        }
        $mask = -1 << (32 - (int) $bits);
        return ($ipLong & $mask) === ($subnetLong & $mask);
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
            
            // Base64 encode subject and description for safe passing via environment variables
            $encodedSubject = base64_encode($subject);
            $encodedDescription = base64_encode($description);
            
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
            
            // Guardar el email en un archivo temporal con codificación UTF-8 sin BOM
            $emailListPath = storage_path('app/email_list_temp.txt');
            file_put_contents($emailListPath, $email, LOCK_EX);
            
            // Configurar las variables para el script Python
            putenv("EMAIL_SUBJECT_B64=" . $encodedSubject);
            putenv("EMAIL_DESCRIPTION_B64=" . $encodedDescription);
            putenv("EMAIL_LIST_PATH=" . $emailListPath);
            
            // Ejecutar el script Python
            $command = 'py ' . base_path() . '/resources/python/generar_emails.py 2>&1';
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

            // Verificar que el script Python realmente envió el correo
            // (puede devolver returnCode 0 pero haber fallado el SMTP por ejemplo)
            $outputStr = implode("\n", $output);
            $sentSuccessfully = $this->emailWasSentSuccessfully($outputStr, $email);

            if (!$sentSuccessfully) {
                \Log::warning('sendTestEmail: El script terminó OK pero el envío SMTP parece haber fallado', [
                    'email' => $email,
                    'output' => $output
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'El script ejecutó pero el envío SMTP falló. Revisa el log de Python.',
                    'output' => $output
                ], 500);
            }

            // Registrar el envío en BD ANTES de que el destinatario abra/clickee.
            // Esto es clave para que las métricas de tasa de apertura sean reales:
            // sin este registro, solo contaríamos a quienes interactuaron.
            $maxIdPerson = Click::where('id_img', $image->id)->max('id_person') ?? 0;
            Click::firstOrCreate(
                [
                    'email' => $email,
                    'id_img' => $image->id,
                ],
                [
                    'id_person' => $maxIdPerson + 1,
                    'email_sent_at' => now(),
                ]
            );

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
        // Contar clicks reales (con clicked_at no nulo)
        $clicksCount = DB::table('clicks')->whereNotNull('clicked_at')->count();
        
        // Contar los correos enviados (registros únicos en la tabla clicks)
        $emailsSent = DB::table('clicks')->distinct()->count('email');
        
        // Contar correos abiertos (con email_opened_at no nulo)
        $opened = DB::table('clicks')->whereNotNull('email_opened_at')->count();

        // Obtener la última imagen subida
        $currentImage = EmailImage::latest()->first();
        
        // Obtener estadísticas por campaña (por imagen)
        $campaignStats = $this->getCampaignStats();

        // Obtener la lista de correos enviados para la última campaña
        $emailList = [];
        if ($currentImage) {
            $emailList = Click::where('id_img', $currentImage->id)
                ->select('email', 'email_sent_at', 'ip_address', 'created_at', 'email_opened_at', 'clicked_at')
                ->orderBy('email_sent_at', 'desc')
                ->get();
        }

        // Log para depuración: verificar el contenido de $emailList en showEstadisticas:
        \Illuminate\Support\Facades\Log::info('Contenido de $emailList en showEstadisticas:', $emailList->toArray());

        return view('estadisticas', compact(
            'clicksCount',
            'opened',
            'emailsSent',
            'currentImage',
            'campaignStats',
            'emailList'
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
                          ->whereNotNull('clicked_at')
                          ->count();
        
        // Los correos abiertos son independientes de los clics
        $opened = Click::where('id_img', $id)
                      ->whereNotNull('email_opened_at')
                      ->count();

        // Obtener la lista de correos enviados para esta campaña (incluye clicked_at para mostrar interacción)
        $emailList = Click::where('id_img', $id)
            ->select('email', 'email_sent_at', 'clicked_at')
            ->orderBy('email_sent_at', 'desc')
            ->get();
        
        // Título para la vista
        $campaignTitle = $campaign->subject ?: 'Campaña ' . $campaign->id;
        
        return view('estadisticas', compact(
            'clicksCount',
            'opened',
            'emailsSent',
            'campaign',
            'campaignStats',
            'campaignTitle',
            'emailList'
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
                              ->whereNotNull('clicked_at')
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
     * Envío masivo simple: archivo + asunto/mensaje manual
     */
    public function sendBulkEmail(Request $request)
    {
        try {
            // Validación básica de la solicitud
            $request->validate([
                'emailFile' => 'required|file|max:10240',
                'subject' => 'required|string|max:255',
                'description' => 'required|string'
            ]);

            // Verificar si hay imagen configurada
            $image = EmailImage::latest()->first();
            if (!$image) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay imagen configurada para enviar'
                ], 400);
            }
            
            // Obtener el archivo y datos del formulario
            $file = $request->file('emailFile');
            $subject = $request->input('subject');
            $description = $request->input('description');
            
            // Asegurarse de que el asunto y la descripción estén en UTF-8
            $subject = mb_convert_encoding($subject, 'UTF-8', 'auto');
            $description = mb_convert_encoding($description, 'UTF-8', 'auto');
            
            // Procesar archivo para obtener lista de emails
            $validEmails = [];
            $filePath = $file->path();
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (in_array($extension, ['xlsx', 'xls'])) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                
                // Buscar emails en todas las celdas
                for ($i = 0; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    for ($j = 0; $j < count($row); $j++) {
                        if (!empty($row[$j])) {
                            $email = trim($row[$j]);
                            if (filter_var($email, FILTER_VALIDATE_EMAIL) && !in_array($email, $validEmails)) {
                                $validEmails[] = $email;
                            }
                        }
                    }
                }
            } elseif ($extension === 'csv') {
                if (($handle = fopen($filePath, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle)) !== FALSE) {
                        foreach ($data as $cell) {
                            $email = trim($cell);
                            if (filter_var($email, FILTER_VALIDATE_EMAIL) && !in_array($email, $validEmails)) {
                                $validEmails[] = $email;
                            }
                        }
                    }
                    fclose($handle);
                }
            } elseif ($extension === 'txt') {
                $content = file_get_contents($filePath);
                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    $email = trim($line);
                    if (filter_var($email, FILTER_VALIDATE_EMAIL) && !in_array($email, $validEmails)) {
                        $validEmails[] = $email;
                    }
                }
            }
            
            if (empty($validEmails)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron emails válidos en el archivo'
                ], 400);
            }
            
            // Crear datos para envío masivo
            $emailData = [];
            foreach ($validEmails as $email) {
                $emailData[] = [
                    'email' => $email,
                    'subject' => $subject,
                    'message' => $description,
                    'priority' => 'normal'
                ];
            }
            
            // Actualizar imagen con datos del formulario
            $image->update([
                'subject' => $subject,
                'description' => $description,
                'priority' => 'normal'
            ]);
            
            // Guardar datos en archivo JSON para Python
            $emailListPath = storage_path('app/email_data_bulk_' . time() . '.json');
            file_put_contents($emailListPath, json_encode($emailData, JSON_UNESCAPED_UNICODE), LOCK_EX);
            
            // Ejecutar script Python
            putenv("EMAIL_DATA_PATH=" . $emailListPath);
            $command = 'py ' . base_path() . '/resources/python/generar_emails.py 2>&1';
            exec($command, $output, $returnCode);
            
            // Limpiar archivo temporal
            @unlink($emailListPath);
            
            if ($returnCode !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar los correos: ' . implode("\n", $output)
                ], 500);
            }

            // Parsear el output del script Python para identificar QUÉ emails
            // se enviaron realmente. El script reporta cada éxito con una línea:
            //   "Email enviado a: usuario@dominio.com - Asunto: ..."
            // Y cada error con:
            //   "Error al enviar email a usuario@dominio.com: ..."
            $outputStr = implode("\n", $output);
            $successfulEmails = $this->parseSuccessfulEmails($outputStr);
            $failedEmails = $this->parseFailedEmails($outputStr);

            \Log::info('sendBulkEmail: Resultados parseados', [
                'total_destinatarios' => count($emailData),
                'enviados_ok' => count($successfulEmails),
                'fallidos' => count($failedEmails),
                'failed_list' => $failedEmails,
            ]);

            // Registrar SOLO los envíos exitosos en BD.
            // Antes se registraban todos, lo que inflaba el contador cuando el SMTP fallaba.
            $now = now();
            $maxIdPerson = Click::where('id_img', $image->id)->max('id_person') ?? 0;
            $registered = 0;

            foreach ($successfulEmails as $index => $sentEmail) {
                Click::firstOrCreate(
                    [
                        'email' => $sentEmail,
                        'id_img' => $image->id,
                    ],
                    [
                        'id_person' => $maxIdPerson + $index + 1,
                        'email_sent_at' => $now,
                    ]
                );
                $registered++;
            }

            return response()->json([
                'success' => true,
                'message' => 'Campaña enviada. ' . $registered . ' de ' . count($emailData) . ' correos enviados exitosamente.',
                'total' => $registered,
                'failed' => count($failedEmails),
                'failed_emails' => $failedEmails,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar: ' . $e->getMessage()
            ], 500);
        }
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
            ]);

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
            $extension = strtolower($file->getClientOriginalExtension());
            
            // Variables para almacenar la información del Excel
            $emailData = []; // Array para guardar todos los datos de cada email
            $defaultSubject = '';
            $defaultMessage = '';
            $defaultPriority = 'normal';
            
            // Procesar archivos Excel
            if (in_array($extension, ['xlsx', 'xls'])) {
                try {
                    // Cargar el archivo Excel usando la biblioteca PhpSpreadsheet
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray();
                    
                    // Buscar los encabezados en la primera fila
                    $headers = array_map('strtolower', $rows[0]);
                    $emailIndex = array_search('email', $headers);
                    $subjectIndex = array_search('asunto', $headers);
                    $messageIndex = array_search('mensaje', $headers);
                    $priorityIndex = array_search('prioridad', $headers);
                    
                    // Si no encontramos los encabezados, intentar con nombres alternativos
                    if ($emailIndex === false) $emailIndex = array_search('correo', $headers);
                    if ($subjectIndex === false) $subjectIndex = array_search('subject', $headers);
                    if ($messageIndex === false) $messageIndex = array_search('message', $headers);
                    if ($priorityIndex === false) $priorityIndex = array_search('priority', $headers);
                    
                    // Procesar cada fila
                    for ($i = 1; $i < count($rows); $i++) {
                        $row = $rows[$i];
                        
                        // Procesar email
                        if ($emailIndex !== false && !empty($row[$emailIndex])) {
                            $email = trim($row[$emailIndex]);
                            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                // Extraer datos individuales para este email
                                $emailSubject = ($subjectIndex !== false && !empty($row[$subjectIndex])) 
                                    ? trim($row[$subjectIndex]) 
                                    : 'Correo importante';
                                
                                $emailMessage = ($messageIndex !== false && !empty($row[$messageIndex])) 
                                    ? trim($row[$messageIndex]) 
                                    : 'Información importante para ti.';
                                
                                $emailPriority = 'normal';
                                if ($priorityIndex !== false && !empty($row[$priorityIndex])) {
                                    $tmpPriority = strtolower(trim($row[$priorityIndex]));
                                    if (in_array($tmpPriority, ['normal', 'high', 'urgent'])) {
                                        $emailPriority = $tmpPriority;
                                    }
                                }
                                
                                // Guardar todos los datos de este email
                                $emailData[] = [
                                    'email' => $email,
                                    'subject' => $emailSubject,
                                    'message' => $emailMessage,
                                    'priority' => $emailPriority
                                ];
                                
                                // Guardar el primer asunto/mensaje como predeterminado para la imagen
                                if (empty($defaultSubject)) {
                                    $defaultSubject = $emailSubject;
                                    $defaultMessage = $emailMessage;
                                    $defaultPriority = $emailPriority;
                                }
                            }
                        }
                    }
                    
                    // Si no se encontraron emails en Excel, dar instrucciones específicas
                    if (empty($emailData)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No se encontraron direcciones de correo válidas en el archivo Excel. El archivo debe tener una columna llamada "email" o "correo".'
                        ], 400);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error procesando archivo Excel: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Error al procesar el archivo Excel. Por favor, asegúrese de que el archivo tenga el formato correcto.'
                    ], 500);
                }
            } elseif ($extension === 'csv') {
                // Procesar archivos CSV
                try {
                    if (($handle = fopen($filePath, "r")) !== FALSE) {
                        // Leer encabezados
                        $headers = array_map('strtolower', fgetcsv($handle));
                        $emailIndex = array_search('email', $headers);
                        $subjectIndex = array_search('asunto', $headers);
                        $messageIndex = array_search('mensaje', $headers);
                        $priorityIndex = array_search('prioridad', $headers);
                        
                        // Si no encontramos los encabezados, intentar con nombres alternativos
                        if ($emailIndex === false) $emailIndex = array_search('correo', $headers);
                        if ($subjectIndex === false) $subjectIndex = array_search('subject', $headers);
                        if ($messageIndex === false) $messageIndex = array_search('message', $headers);
                        if ($priorityIndex === false) $priorityIndex = array_search('priority', $headers);
                        
                        // Procesar cada fila
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            // Procesar email
                            if ($emailIndex !== false && !empty($data[$emailIndex])) {
                                $email = trim($data[$emailIndex]);
                                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                    // Extraer datos individuales para este email
                                    $emailSubject = ($subjectIndex !== false && !empty($data[$subjectIndex])) 
                                        ? trim($data[$subjectIndex]) 
                                        : 'Correo importante';
                                    
                                    $emailMessage = ($messageIndex !== false && !empty($data[$messageIndex])) 
                                        ? trim($data[$messageIndex]) 
                                        : 'Información importante para ti.';
                                    
                                    $emailPriority = 'normal';
                                    if ($priorityIndex !== false && !empty($data[$priorityIndex])) {
                                        $tmpPriority = strtolower(trim($data[$priorityIndex]));
                                        if (in_array($tmpPriority, ['normal', 'high', 'urgent'])) {
                                            $emailPriority = $tmpPriority;
                                        }
                                    }
                                    
                                    // Guardar todos los datos de este email
                                    $emailData[] = [
                                        'email' => $email,
                                        'subject' => $emailSubject,
                                        'message' => $emailMessage,
                                        'priority' => $emailPriority
                                    ];
                                    
                                    // Guardar el primer asunto/mensaje como predeterminado para la imagen
                                    if (empty($defaultSubject)) {
                                        $defaultSubject = $emailSubject;
                                        $defaultMessage = $emailMessage;
                                        $defaultPriority = $emailPriority;
                                    }
                                }
                            }
                        }
                        fclose($handle);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error procesando archivo CSV: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Error al procesar el archivo CSV: ' . $e->getMessage()
                    ], 500);
                }
            }
            
            // Verificar si se encontraron emails
            if (empty($emailData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron direcciones de correo válidas en el archivo'
                ], 400);
            }
            
            // Actualizar la imagen con la información del Excel (usando el primer registro)
            $image->update([
                'subject' => $defaultSubject ?: 'Correo importante',
                'description' => $defaultMessage ?: 'Información importante para ti.',
                'priority' => $defaultPriority
            ]);
            
            // Guardar los datos de correos en un archivo JSON temporal para el script Python
            $emailListPath = storage_path('app/email_data_bulk_' . time() . '.json');
            $emailContent = json_encode($emailData, JSON_UNESCAPED_UNICODE);
            file_put_contents($emailListPath, $emailContent, LOCK_EX);
            
            // Configurar las variables para el script Python
            putenv("EMAIL_DATA_PATH=" . $emailListPath);
            
            // Ejecutar el script Python
            $command = 'py ' . base_path() . '/resources/python/generar_emails.py 2>&1';
            exec($command, $output, $returnCode);
            
            \Log::info('Resultado del script Python para envío masivo:', [
                'return_code' => $returnCode,
                'output' => $output,
                'total_emails' => count($emailData)
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

            // Parsear output Python para identificar enviados vs fallidos
            // (mismo motivo que en sendBulkEmail: returnCode 0 no garantiza envío SMTP).
            $outputStr = implode("\n", $output);
            $successfulEmails = $this->parseSuccessfulEmails($outputStr);
            $failedEmails = $this->parseFailedEmails($outputStr);

            \Log::info('uploadEmailList: Resultados parseados', [
                'total_destinatarios' => count($emailData),
                'enviados_ok' => count($successfulEmails),
                'fallidos' => count($failedEmails),
            ]);

            // Registrar SOLO los envíos exitosos en BD
            $now = now();
            $maxIdPerson = Click::where('id_img', $image->id)->max('id_person') ?? 0;
            $registered = 0;

            foreach ($successfulEmails as $index => $sentEmail) {
                Click::firstOrCreate(
                    [
                        'email' => $sentEmail,
                        'id_img' => $image->id,
                    ],
                    [
                        'id_person' => $maxIdPerson + $index + 1,
                        'email_sent_at' => $now,
                    ]
                );
                $registered++;
            }

            // Respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Correos enviados. ' . $registered . ' de ' . count($emailData) . ' exitosos.',
                'total' => $registered,
                'failed' => count($failedEmails),
                'failed_emails' => $failedEmails,
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

    /**
     * Previsualiza el contenido del archivo Excel
     */
    public function previewExcel(Request $request)
    {
        try {
            // Validación básica de la solicitud
            $request->validate([
                'emailFile' => 'required|file|max:10240', // 10MB máximo
            ]);
            
            // Obtener el archivo subido
            $file = $request->file('emailFile');
            $filePath = $file->path();
            $extension = strtolower($file->getClientOriginalExtension());
            
            // Variables para almacenar la información del Excel
            $validEmails = [];
            $subject = '';
            $message = '';
            $priority = 'normal';
            $totalEmails = 0;
            
            if (in_array($extension, ['xlsx', 'xls'])) {
                // Cargar el archivo Excel usando PhpSpreadsheet
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                
                // Buscar los encabezados en la primera fila
                $headers = array_map('strtolower', $rows[0]);
                $emailIndex = array_search('email', $headers);
                $subjectIndex = array_search('asunto', $headers);
                $messageIndex = array_search('mensaje', $headers);
                $priorityIndex = array_search('prioridad', $headers);
                
                // Si no encontramos los encabezados, intentar con nombres alternativos
                if ($emailIndex === false) $emailIndex = array_search('correo', $headers);
                if ($subjectIndex === false) $subjectIndex = array_search('subject', $headers);
                if ($messageIndex === false) $messageIndex = array_search('message', $headers);
                if ($priorityIndex === false) $priorityIndex = array_search('priority', $headers);
                
                // Si no se encontró columna específica de email, buscar en todas las columnas
                if ($emailIndex === false) {
                    for ($i = 0; $i < count($rows); $i++) {
                        $row = $rows[$i];
                        for ($j = 0; $j < count($row); $j++) {
                            if (!empty($row[$j])) {
                                $email = trim($row[$j]);
                                if (filter_var($email, FILTER_VALIDATE_EMAIL) && !in_array($email, $validEmails)) {
                                    $validEmails[] = $email;
                                }
                            }
                        }
                    }
                } else {
                    // Procesar cada fila usando la columna específica
                    for ($i = 1; $i < count($rows); $i++) {
                        $row = $rows[$i];
                        
                        // Procesar email
                        if (!empty($row[$emailIndex])) {
                            $email = trim($row[$emailIndex]);
                            if (filter_var($email, FILTER_VALIDATE_EMAIL) && !in_array($email, $validEmails)) {
                                $validEmails[] = $email;
                            }
                        }
                    }
                }
                
                // Obtener datos adicionales solo si se encontró columna específica
                if ($emailIndex !== false) {
                    for ($i = 1; $i < count($rows); $i++) {
                        $row = $rows[$i];
                        
                        // Obtener el asunto de la primera fila válida
                        if (empty($subject) && $subjectIndex !== false && !empty($row[$subjectIndex])) {
                            $subject = trim($row[$subjectIndex]);
                        }
                        
                        // Obtener el mensaje de la primera fila válida
                        if (empty($message) && $messageIndex !== false && !empty($row[$messageIndex])) {
                            $message = trim($row[$messageIndex]);
                        }
                        
                        // Obtener la prioridad de la primera fila válida
                        if ($priority === 'normal' && $priorityIndex !== false && !empty($row[$priorityIndex])) {
                            $tmpPriority = strtolower(trim($row[$priorityIndex]));
                            if (in_array($tmpPriority, ['normal', 'high', 'urgent'])) {
                                $priority = $tmpPriority;
                            }
                        }
                    }
                }
            } elseif ($extension === 'csv') {
                if (($handle = fopen($filePath, "r")) !== FALSE) {
                    // Leer encabezados
                    $headers = array_map('strtolower', fgetcsv($handle));
                    $emailIndex = array_search('email', $headers);
                    $subjectIndex = array_search('asunto', $headers);
                    $messageIndex = array_search('mensaje', $headers);
                    $priorityIndex = array_search('prioridad', $headers);
                    
                    // Si no encontramos los encabezados, intentar con nombres alternativos
                    if ($emailIndex === false) $emailIndex = array_search('correo', $headers);
                    if ($subjectIndex === false) $subjectIndex = array_search('subject', $headers);
                    if ($messageIndex === false) $messageIndex = array_search('message', $headers);
                    if ($priorityIndex === false) $priorityIndex = array_search('priority', haystack: $headers);
                    
                    // Procesar cada fila
                    while (($data = fgetcsv($handle)) !== FALSE) {
                        // Procesar email
                        if ($emailIndex !== false && !empty($data[$emailIndex])) {
                            $email = trim($data[$emailIndex]);
                            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $validEmails[] = $email;
                            }
                        }
                        
                        // Obtener el asunto de la primera fila válida
                        if (empty($subject) && $subjectIndex !== false && !empty($data[$subjectIndex])) {
                            $subject = trim($data[$subjectIndex]);
                        }
                        
                        // Obtener el mensaje de la primera fila válida
                        if (empty($message) && $messageIndex !== false && !empty($data[$messageIndex])) {
                            $message = trim($data[$messageIndex]);
                        }
                        
                        // Obtener la prioridad de la primera fila válida
                        if ($priority === 'normal' && $priorityIndex !== false && !empty($data[$priorityIndex])) {
                            $tmpPriority = strtolower(trim($data[$priorityIndex]));
                            if (in_array($tmpPriority, ['normal', 'high', 'urgent'])) {
                                $priority = $tmpPriority;
                            }
                        }
                    }
                    fclose($handle);
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'subject' => $subject ?: '',
                    'message' => $message ?: '',
                    'priority' => $priority,
                    'totalEmails' => count($validEmails),
                    'sampleEmails' => array_slice($validEmails, 0, 5) // Mostrar solo los primeros 5 emails como muestra
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCampaignPerformance()
    {
        try {
            $campaigns = EmailImage::with('clicks')->get();

            $performanceData = [];
            foreach ($campaigns as $campaign) {
                // Contar correctamente:
                // - total_sent: registros con email_sent_at NO NULL
                //   (antes contaba TODOS los registros, incluidos los que solo
                //    se crearon al abrir/clickear sin un envío real registrado).
                // - emails únicos para no duplicar por reenvíos.
                $totalSent = $campaign->clicks
                    ->whereNotNull('email_sent_at')
                    ->unique('email')
                    ->count();

                $totalOpened = $campaign->clicks
                    ->whereNotNull('email_opened_at')
                    ->unique('email')
                    ->count();

                $totalClicked = $campaign->clicks
                    ->whereNotNull('clicked_at')
                    ->unique('email')
                    ->count();

                // Tasa de apertura: abiertos / enviados
                $openRate = $totalSent > 0 ? ($totalOpened / $totalSent) * 100 : 0;
                // Tasa de clic (CTR): clickeados / enviados (más útil que clic/abierto)
                $clickRate = $totalSent > 0 ? ($totalClicked / $totalSent) * 100 : 0;
                // Tasa clic-a-apertura: cuánto de los que abrieron también hizo clic
                $clickToOpenRate = $totalOpened > 0 ? ($totalClicked / $totalOpened) * 100 : 0;

                $performanceData[] = [
                    'campaign_id' => $campaign->id,
                    'subject' => $campaign->subject,
                    'description' => $campaign->description,
                    'priority' => $campaign->priority,
                    'link_redirection' => $campaign->link_redirection,
                    'created_at' => $campaign->created_at->format('d/m/Y H:i'),
                    'total_sent' => $totalSent,
                    'total_opened' => $totalOpened,
                    'total_clicked' => $totalClicked,
                    'open_rate' => round($openRate, 2),
                    'click_rate' => round($clickRate, 2),
                    'click_to_open_rate' => round($clickToOpenRate, 2),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $performanceData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el rendimiento de las campañas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function predictCampaignPerformance(Request $request)
    {
        try {
            // 1. Obtener datos históricos de las campañas
            $campaignPerformanceResponse = $this->getCampaignPerformance();
            $campaignPerformanceData = json_decode($campaignPerformanceResponse->getContent(), true)['data'];

            if (empty($campaignPerformanceData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay datos históricos de campañas para realizar predicciones.'
                ], 400);
            }

            // 2. Construir el prompt para la IA
            $prompt = "A continuación, se presentan datos históricos de campañas de marketing por correo electrónico con sus métricas de rendimiento (total enviados, abiertos, clics, tasa de apertura, tasa de clic). Basándose en estos datos, predice qué tan eficiente será una NUEVA campaña. También proporciona ideas adicionales y consejos relevantes para mejorar el rendimiento de futuras campañas. Describe tus predicciones y consejos en un formato claro y conciso, idealmente con viñetas o un pequeño párrafo para cada recomendación.\n\n";

            foreach ($campaignPerformanceData as $campaign) {
                $prompt .= "Campaña ID: {$campaign['campaign_id']}\n";
                $prompt .= "  Asunto: \"{$campaign['subject']}\"\n";
                $prompt .= "  Descripción: \"{$campaign['description']}\"\n";
                $prompt .= "  Link: {$campaign['link_redirection']}\n";
                $prompt .= "  Enviados: {$campaign['total_sent']}\n";
                $prompt .= "  Abiertos: {$campaign['total_opened']}\n";
                $prompt .= "  Clics: {$campaign['total_clicked']}\n";
                $prompt .= "  Tasa de Apertura: {$campaign['open_rate']}%\n";
                $prompt .= "  Tasa de Clic: {$campaign['click_rate']}%\n\n";
            }

            $prompt .= "Predicciones y Recomendaciones para FUTURAS campañas:\n";

            // 3. Realizar la solicitud a la API compatible (LM Studio u otra)
            $aiResponse = $this->createChatCompletion(
                'Eres un experto en marketing digital y análisis de campañas de correo electrónico.',
                $prompt,
                500,
                0.7
            );

            // Verificar si hay error en la respuesta
            if (isset($aiResponse['error'])) {
                \Log::error('AI Predictions Error:', ['error' => $aiResponse['error']]);
                return response()->json([
                    'success' => false,
                    'message' => $aiResponse['error']['message'],
                    'error' => $aiResponse['error']
                ], 500);
            }

            if (isset($aiResponse['choices'][0]['message']['content'])) {
                return response()->json([
                    'success' => true,
                    'predictions_and_recommendations' => $aiResponse['choices'][0]['message']['content']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo obtener una respuesta válida de la IA.',
                    'error' => $aiResponse
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al comunicarse con la IA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint principal del chatbot.
     *
     * Mejoras vs versión original:
     *  1) Detección de intención (intent) por keywords → solo inyectamos el
     *     contexto relevante a cada pregunta (horarios / campaña específica /
     *     general). Antes mandábamos TODO el histórico siempre, lo que hacía
     *     que el LLM tardara más y a veces se confundiera.
     *  2) Soporte de streaming (SSE) → si el cliente envía "stream=true", la
     *     respuesta llega token-por-token y la UX se siente instantánea aunque
     *     la generación total tarde lo mismo.
     *  3) max_tokens reducido (400) y temperature más baja (0.4) → respuestas
     *     más rápidas y centradas en los datos, no creativas.
     *  4) System prompt más estricto: "responde solo con los datos provistos,
     *     no inventes". Esto reduce alucinaciones.
     */
    public function askAIAboutCampaigns(Request $request)
    {
        try {
            $request->validate([
                'question' => 'required|string|max:1000',
                'stream'   => 'sometimes|boolean',
            ]);

            $userQuestion = trim($request->input('question'));
            $useStream    = $request->boolean('stream', false);

            // 1) Detectar el tipo de pregunta para inyectar el contexto correcto.
            $intent = $this->detectQuestionIntent($userQuestion);
            \Log::info('Chatbot: intent detectado', ['intent' => $intent, 'question' => $userQuestion]);

            // 2) Construir contexto MÍNIMO necesario según el intent.
            $context = $this->buildContextForIntent($intent, $userQuestion);

            // 3) System prompt corto y firme.
            $systemPrompt = 'Eres un asistente experto en email marketing que responde preguntas sobre las campañas del usuario. Reglas estrictas: (1) Responde SIEMPRE en español, breve y directo. (2) Usa SOLO los datos provistos en el contexto. (3) Si los datos no alcanzan para responder, dilo claramente: "No tengo suficientes datos para responder eso". (4) Cuando des números, cítalos textualmente del contexto. (5) Formatea con viñetas si listas más de 2 elementos.';

            // 4) Prompt final compacto.
            $prompt = $context . "\nPREGUNTA DEL USUARIO: " . $userQuestion;

            // 5) Streaming o respuesta completa.
            if ($useStream) {
                return $this->streamChatCompletion($systemPrompt, $prompt);
            }

            // Fallback no-streaming (compatibilidad).
            $aiResponse = $this->createChatCompletion($systemPrompt, $prompt, 400, 0.4);

            if (isset($aiResponse['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $aiResponse['error']['message'],
                    'error'   => $aiResponse['error'],
                ], 500);
            }

            if (isset($aiResponse['choices'][0]['message']['content'])) {
                return response()->json([
                    'success'  => true,
                    'response' => $aiResponse['choices'][0]['message']['content'],
                    'intent'   => $intent,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener una respuesta válida de la IA.',
                'error'   => $aiResponse,
            ], 500);

        } catch (\Exception $e) {
            \Log::error('Chatbot Exception:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al comunicarse con la IA: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clasifica la pregunta del usuario en uno de varios "intents" mediante
     * coincidencia de keywords. Es rapidísimo (no llama al LLM para clasificar).
     *
     * Intents posibles:
     *   - 'schedule'         → mejor hora/día para enviar
     *   - 'campaign_metric'  → métrica concreta de una campaña ("cuántos abrieron la campaña 3")
     *   - 'overview'         → resumen general / lista de campañas
     *   - 'recommendation'   → consejos para mejorar
     *   - 'general'          → cualquier otra pregunta
     */
    private function detectQuestionIntent(string $question): string
    {
        $q = mb_strtolower($question);

        // Helper para chequear si alguna keyword aparece
        $contains = function (array $keywords) use ($q) {
            foreach ($keywords as $kw) {
                if (mb_strpos($q, $kw) !== false) return true;
            }
            return false;
        };

        // 0) PREGUNTAS EDUCATIVAS / DEFINICIONES → general
        // Si pregunta "qué es X", "explícame X", "qué significa X" tiene prioridad
        // sobre las otras detecciones (no queremos inyectar métricas si solo
        // quiere una definición).
        if ($contains([
            'que es', 'qué es', 'qué significa', 'que significa',
            'explicame', 'explícame', 'explica ', 'definicion', 'definición',
            'qué quiere decir', 'que quiere decir',
        ])) {
            return 'general';
        }

        // 1) RECOMENDACIONES → tiene prioridad sobre "métrica" porque "mejorar
        // mi tasa de apertura" es claramente una petición de consejo, no un
        // dato puntual.
        if ($contains([
            'recomienda', 'recomendacion', 'recomendación',
            'consejo', 'mejorar', 'que hago', 'qué hago',
            'tip', 'sugiere', 'sugerencia', 'cómo aumentar', 'como aumentar',
            'cómo mejorar', 'como mejorar', 'cómo subir', 'como subir',
        ])) {
            return 'recommendation';
        }

        // 2) HORARIOS - "cuándo enviar", "mejor hora", "qué día", etc.
        $hasCuando = $contains(['cuando', 'cuándo']);
        $hasEnviar = $contains(['enviar', 'envio', 'envío', 'mandar', 'campaña', 'campana', 'correo', 'email']);
        if ($hasCuando && $hasEnviar) {
            return 'schedule';
        }
        if ($contains([
            'hora', 'horario', 'mejor momento', 'mejor dia', 'mejor día',
            'que dia', 'qué día', 'qué hora', 'que hora',
            'mañana', 'tarde', 'noche', 'madrugada',
            'lunes', 'martes', 'miercoles', 'miércoles',
            'jueves', 'viernes', 'sabado', 'sábado', 'domingo',
            'momento ideal', 'dia ideal', 'día ideal',
        ])) {
            return 'schedule';
        }

        // 3) MÉTRICA DE CAMPAÑA ESPECÍFICA
        $isCampaignSpecific = (
            preg_match('/campa[nñ]a\s*[#"\']?\s*(\d+|esta|esa|aquella|actual|reciente|ultima|última|primera|nueva)/iu', $q)
            || preg_match('/\b(esta|esa|aquella|actual|reciente|ultima|última|primera|nueva)\s+campa[nñ]a/iu', $q)
            || $contains([
                'cuantos correos', 'cuántos correos',
                'cuantos abrieron', 'cuántos abrieron',
                'cuantos leyeron', 'cuántos leyeron',
                'cuantos clickearon', 'cuántos clickearon',
                'cuantas personas', 'cuántas personas',
                'numero de aperturas', 'número de aperturas',
                'tasa de apertura', 'tasa de clic',
                'estadisticas de', 'estadísticas de',
                'datos de', 'metricas de', 'métricas de',
                'mejor desempeño', 'mejor desempeno', 'peor desempeño', 'peor desempeno',
                'mejor campaña', 'mejor campana', 'peor campaña', 'peor campana',
            ])
        );
        if ($isCampaignSpecific) {
            return 'campaign_metric';
        }

        // 4) OVERVIEW / LISTA
        if ($contains([
            'todas las campañas', 'todas las campanas',
            'lista de campañas', 'lista de campanas',
            'resumen', 'cuantas campañas', 'cuántas campañas',
            'cuantas campanas', 'cuántas campanas',
        ])) {
            return 'overview';
        }

        return 'general';
    }

    /**
     * Construye el bloque de contexto que se le pasa al LLM según el intent.
     * La idea es enviar el MÍNIMO de datos necesarios para que la respuesta
     * sea rápida y precisa.
     */
    private function buildContextForIntent(string $intent, string $question): string
    {
        switch ($intent) {
            case 'schedule':
                return $this->buildScheduleContext();

            case 'campaign_metric':
                return $this->buildCampaignMetricContext($question);

            case 'overview':
                return $this->buildOverviewContext();

            case 'recommendation':
                return $this->buildOverviewContext() . "\n(Usa estos datos como base para tus recomendaciones.)\n";

            case 'general':
            default:
                return $this->buildOverviewContext();
        }
    }

    /**
     * Contexto compacto con métricas resumidas de TODAS las campañas.
     */
    private function buildOverviewContext(): string
    {
        $resp = $this->getCampaignPerformance();
        $data = json_decode($resp->getContent(), true)['data'] ?? [];

        if (empty($data)) {
            return "CONTEXTO: No hay campañas registradas todavía.\n";
        }

        $ctx = "CONTEXTO - Resumen de campañas:\n";
        $ctx .= "Total de campañas: " . count($data) . "\n\n";
        foreach ($data as $c) {
            $ctx .= "• Campaña #{$c['campaign_id']} \"{$c['subject']}\": "
                  . "enviados={$c['total_sent']}, abiertos={$c['total_opened']}, "
                  . "clics={$c['total_clicked']}, "
                  . "tasa_apertura={$c['open_rate']}%, "
                  . "CTR={$c['click_rate']}%\n";
        }
        return $ctx . "\n";
    }

    /**
     * Contexto enfocado en horarios y días óptimos (reutiliza
     * computeOpenStatsByTime que ya teníamos).
     */
    private function buildScheduleContext(): string
    {
        $stats = $this->computeOpenStatsByTime();

        if ($stats['total_opens'] < 5) {
            return "CONTEXTO: Solo hay {$stats['total_opens']} aperturas registradas, datos insuficientes para recomendar horarios con confianza.\n";
        }

        $ctx = "CONTEXTO - Patrón de aperturas (zona horaria: {$stats['timezone']}):\n";
        $ctx .= "Total aperturas analizadas: {$stats['total_opens']}\n\n";

        $ctx .= "Top 3 horas con más aperturas:\n";
        foreach ($stats['top_hours'] as $h) {
            $ctx .= "  - {$h['hour']}: {$h['opens']} aperturas\n";
        }

        $ctx .= "\nTop 3 días con más aperturas:\n";
        foreach ($stats['top_days'] as $d) {
            $ctx .= "  - {$d['day']}: {$d['opens']} aperturas\n";
        }

        $ctx .= "\nMejores combinaciones día+hora:\n";
        foreach ($stats['top_slots'] as $s) {
            $ctx .= "  - {$s['day']} a las {$s['hour']}: {$s['opens']} aperturas\n";
        }

        return $ctx . "\n";
    }

    /**
     * Contexto para preguntas sobre una campaña específica. Intenta extraer
     * el ID de la pregunta; si no, devuelve todas las campañas y deja que
     * el LLM identifique cuál.
     */
    private function buildCampaignMetricContext(string $question): string
    {
        $resp = $this->getCampaignPerformance();
        $data = json_decode($resp->getContent(), true)['data'] ?? [];

        if (empty($data)) {
            return "CONTEXTO: No hay campañas registradas.\n";
        }

        // Intentar extraer ID de la pregunta: "campaña 3", "campaña #5", etc.
        $matchedId = null;
        if (preg_match('/campa[nñ]a\s*#?\s*(\d+)/iu', $question, $m)) {
            $matchedId = (int) $m[1];
        }

        // O detectar "la última" / "la actual" / "la reciente"
        $isLatest = preg_match('/\b(ultima|última|actual|reciente|nueva)\b/iu', $question);

        if ($matchedId) {
            $campaign = collect($data)->firstWhere('campaign_id', $matchedId);
            if ($campaign) {
                return $this->renderCampaignDetail($campaign);
            }
            return "CONTEXTO: No existe una campaña con ID {$matchedId}. Campañas existentes: "
                 . implode(', ', array_column($data, 'campaign_id')) . ".\n";
        }

        if ($isLatest) {
            // La última (la primera del array si vienen ordenadas desc, sino la de mayor id)
            $latest = collect($data)->sortByDesc('campaign_id')->first();
            return "CONTEXTO - Datos de la campaña más reciente:\n" . $this->renderCampaignDetail($latest);
        }

        // Sin ID claro: incluir todas las campañas con detalle moderado.
        $ctx = "CONTEXTO - Métricas detalladas de todas las campañas:\n";
        foreach ($data as $c) {
            $ctx .= $this->renderCampaignDetail($c) . "\n";
        }
        return $ctx;
    }

    private function renderCampaignDetail(array $c): string
    {
        return "• Campaña #{$c['campaign_id']} \"{$c['subject']}\"\n"
             . "    Enviada: {$c['created_at']}\n"
             . "    Correos enviados: {$c['total_sent']}\n"
             . "    Correos abiertos (leídos): {$c['total_opened']}\n"
             . "    Clics recibidos: {$c['total_clicked']}\n"
             . "    Tasa de apertura: {$c['open_rate']}%\n"
             . "    Tasa de clic (CTR): {$c['click_rate']}%\n"
             . "    Tasa clic-a-apertura: " . ($c['click_to_open_rate'] ?? 0) . "%\n";
    }

    /**
     * Hace streaming Server-Sent Events del LLM hacia el cliente.
     *
     * Conecta a LM Studio con stream:true, lee cada chunk SSE, extrae el
     * texto del delta y lo reenvía al navegador en formato simple para
     * que el frontend lo pueda renderizar progresivamente.
     */
    private function streamChatCompletion(string $systemPrompt, string $userPrompt)
    {
        $baseUrl = rtrim(env('LLM_BASE_URL', 'http://localhost:1234/v1'), '/');
        $apiKey  = env('LLM_API_KEY', 'lm-studio');
        $model   = env('LLM_MODEL', 'deepseek-r1-0528-qwen3-8b');
        $endpoint = $baseUrl . '/chat/completions';

        $payload = [
            'model'       => $model,
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'max_tokens'  => 400,
            'temperature' => 0.4,
            'stream'      => true,
        ];

        return response()->stream(function () use ($endpoint, $payload, $apiKey) {
            // Desactivar buffering de salida para que cada chunk se envíe al instante.
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', false);
            while (ob_get_level() > 0) { @ob_end_flush(); }

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                    'Accept: text/event-stream',
                ],
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_TIMEOUT        => 180,
                CURLOPT_RETURNTRANSFER => false,
                // Callback: se ejecuta cada vez que cURL recibe un chunk de LM Studio.
                CURLOPT_WRITEFUNCTION  => function ($ch, $data) {
                    // LM Studio devuelve líneas tipo: "data: {...json...}\n\n"
                    // Puede llegar más de una en un solo chunk.
                    $lines = preg_split('/\r?\n/', $data);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if ($line === '' || strpos($line, 'data:') !== 0) continue;

                        $payload = trim(substr($line, 5));
                        if ($payload === '[DONE]') {
                            echo "event: done\ndata: [DONE]\n\n";
                            @flush();
                            continue;
                        }

                        $json = json_decode($payload, true);
                        if (!is_array($json)) continue;

                        // Extraer el delta de contenido (puede no venir en chunks de control).
                        $delta = $json['choices'][0]['delta']['content'] ?? '';
                        if ($delta !== '') {
                            // Reenviar como SSE simple para el cliente: data: {"t":"texto"}
                            echo 'data: ' . json_encode(['t' => $delta], JSON_UNESCAPED_UNICODE) . "\n\n";
                            @flush();
                        }
                    }
                    return strlen($data);
                },
            ]);
            curl_exec($ch);

            if (curl_errno($ch)) {
                $err = curl_error($ch);
                \Log::error('Streaming cURL error', ['error' => $err]);
                echo 'data: ' . json_encode(['error' => $err], JSON_UNESCAPED_UNICODE) . "\n\n";
                @flush();
            }
            curl_close($ch);
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store, must-revalidate',
            'Connection'        => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Para Nginx; ignorado por Apache pero no hace daño
        ]);
    }

    public function getEmailImageRecommendations(Request $request)
    {
        try {
            // Obtener datos históricos de las campañas
            $campaignPerformanceResponse = $this->getCampaignPerformance();
            $campaignPerformanceData = json_decode($campaignPerformanceResponse->getContent(), true)['data'];

            // Construir el prompt específico para recomendaciones de imágenes de correo
            $prompt = "Eres un experto en marketing digital y diseño de campañas de email marketing. Necesito que me ayudes a crear contenido efectivo para imágenes de correo electrónico.\n\n";

            if (!empty($campaignPerformanceData)) {
                $prompt .= "Basándote en el historial de mis campañas anteriores:\n\n";
                foreach ($campaignPerformanceData as $campaign) {
                    $prompt .= "• Campaña: \"{$campaign['subject']}\"\n";
                    $prompt .= "  - Tasa de Apertura: {$campaign['open_rate']}%\n";
                    $prompt .= "  - Tasa de Clic: {$campaign['click_rate']}%\n";
                    $prompt .= "  - Enviados: {$campaign['total_sent']}\n\n";
                }
                $prompt .= "Proporciona recomendaciones específicas para mejorar el contenido de las imágenes de correo:\n";
            } else {
                $prompt .= "Como no hay datos históricos disponibles, proporciona recomendaciones generales pero efectivas para crear imágenes de correo que generen engagement:\n";
            }

            $prompt .= "1. Elementos visuales que aumentan la tasa de apertura\n";
            $prompt .= "2. Tipografías y colores recomendados\n";
            $prompt .= "3. Estructura y composición de la imagen\n";
            $prompt .= "4. Call-to-action efectivos\n";
            $prompt .= "5. Mejores prácticas para diferentes tipos de campañas\n";
            $prompt .= "6. Consejos para optimizar en dispositivos móviles\n\n";
            $prompt .= "Responde de manera clara y estructurada, con ejemplos prácticos cuando sea posible.";

            // Realizar la solicitud a la API compatible (LM Studio u otra)
            $aiResponse = $this->createChatCompletion(
                'Eres un experto en marketing digital, diseño gráfico y optimización de campañas de email marketing. Especialista en crear contenido visual que genera engagement y conversiones.',
                $prompt,
                800,
                0.7
            );

            // Verificar si hay error en la respuesta
            if (isset($aiResponse['error'])) {
                \Log::error('AI Recommendations Error:', ['error' => $aiResponse['error']]);
                return response()->json([
                    'success' => false,
                    'message' => $aiResponse['error']['message'],
                    'error' => $aiResponse['error']
                ], 500);
            }

            if (isset($aiResponse['choices'][0]['message']['content'])) {
                return response()->json([
                    'success' => true,
                    'recommendations' => $aiResponse['choices'][0]['message']['content']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo obtener recomendaciones de la IA.',
                    'error' => $aiResponse
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('AI Email Image Recommendations Exception:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener recomendaciones de la IA: ' . $e->getMessage()
            ], 500);
        }
    }

    public function askAIAboutEmailContent(Request $request)
    {
        try {
            $request->validate([
                'question' => 'required|string|max:1000',
                'image_context' => 'nullable|string|max:500'
            ]);

            $userQuestion = $request->input('question');
            $imageContext = $request->input('image_context', '');

            // Construir el prompt contextual
            $prompt = "Eres un experto en marketing digital y diseño de campañas de email marketing. ";
            
            if ($imageContext) {
                $prompt .= "Contexto de la imagen actual: {$imageContext}\n\n";
            }
            
            $prompt .= "Pregunta del usuario: {$userQuestion}\n\n";
            $prompt .= "Proporciona una respuesta útil, práctica y específica para mejorar el contenido de la imagen de correo. Incluye ejemplos concretos cuando sea posible.";

            // Realizar la solicitud a la API compatible (LM Studio u otra)
            $aiResponse = $this->createChatCompletion(
                'Eres un asistente experto en marketing digital y diseño de campañas de email. Especialista en optimizar contenido visual para aumentar engagement y conversiones.',
                $prompt,
                600,
                0.7
            );

            // Verificar si hay error en la respuesta
            if (isset($aiResponse['error'])) {
                \Log::error('AI Email Content Error:', ['error' => $aiResponse['error']]);
                return response()->json([
                    'success' => false,
                    'message' => $aiResponse['error']['message'],
                    'error' => $aiResponse['error']
                ], 500);
            }

            if (isset($aiResponse['choices'][0]['message']['content'])) {
                return response()->json([
                    'success' => true,
                    'response' => $aiResponse['choices'][0]['message']['content']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo obtener una respuesta válida de la IA.',
                    'error' => $aiResponse
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('AI Email Content Question Exception:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al comunicarse con la IA: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ====================================================================
     * RECOMENDACIÓN DE HORARIOS ÓPTIMOS DE ENVÍO
     *
     * Estos métodos analizan las aperturas históricas (campo email_opened_at
     * de la tabla clicks) y las agrupan por hora del día y día de la semana.
     * Luego le pasan esos datos a la IA para que recomiende cuándo enviar
     * la próxima campaña.
     * ====================================================================*/

    /**
     * Endpoint público que devuelve estadísticas de aperturas por hora/día.
     * Útil para alimentar gráficas en el frontend o para auditar los datos
     * que se le pasan a la IA.
     */
    public function getOpenStatsByTime()
    {
        try {
            $data = $this->computeOpenStatsByTime();
            return response()->json([
                'success' => true,
                'timezone' => config('app.timezone'),
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error('getOpenStatsByTime error', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo estadísticas por hora: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint que arma un prompt con las estadísticas por hora/día y pide
     * a la IA una recomendación concreta de horarios óptimos de envío.
     */
    public function getOptimalSendTimeRecommendation()
    {
        try {
            $stats = $this->computeOpenStatsByTime();

            // Si no hay datos suficientes, evitamos llamar a la IA con vacío.
            if ($stats['total_opens'] < 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay suficientes aperturas registradas para generar una recomendación basada en datos. Se necesitan al menos 5 aperturas. Actual: ' . $stats['total_opens'] . '.',
                    'data' => $stats,
                ], 400);
            }

            // Construir un prompt rico en datos para la IA.
            $prompt = $this->buildSendTimePrompt($stats);

            \Log::info('Optimal Send Time Prompt:', ['prompt' => $prompt]);

            $aiResponse = $this->createChatCompletion(
                'Eres un experto en email marketing y análisis de datos. Tu trabajo es analizar patrones de apertura de correos y recomendar los mejores horarios y días para enviar campañas. Responde SIEMPRE en español, de forma clara, estructurada y accionable. Justifica tus recomendaciones con los datos provistos.',
                $prompt,
                700,
                0.5  // Más bajo que en otros endpoints porque queremos respuestas basadas en datos, no creativas.
            );

            if (isset($aiResponse['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $aiResponse['error']['message'],
                    'error' => $aiResponse['error'],
                    'data' => $stats,
                ], 500);
            }

            if (isset($aiResponse['choices'][0]['message']['content'])) {
                return response()->json([
                    'success' => true,
                    'recommendation' => $aiResponse['choices'][0]['message']['content'],
                    'data' => $stats,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se obtuvo respuesta válida de la IA.',
                'error' => $aiResponse,
                'data' => $stats,
            ], 500);

        } catch (\Exception $e) {
            \Log::error('getOptimalSendTimeRecommendation error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al generar recomendación: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calcula las estadísticas de aperturas agrupadas por hora del día (0-23)
     * y día de la semana (0=Domingo, 6=Sábado).
     *
     * Devuelve también totales y el "top 3" de mejores horarios/días.
     */
    private function computeOpenStatsByTime(): array
    {
        // Traer todas las aperturas con su timestamp.
        $opens = Click::whereNotNull('email_opened_at')
            ->select('email_opened_at')
            ->get();

        // Zona horaria objetivo: la configurada en config/app.php
        // (recomendado poner APP_TIMEZONE=America/Mexico_City en .env).
        // Esto es CRÍTICO: si la BD guarda en UTC y aquí no convertimos,
        // un correo abierto a las 14:00 hora local aparecería como 20:00 UTC
        // y la IA recomendaría malos horarios.
        $appTz = config('app.timezone', 'UTC');

        // Inicializar matrices con ceros para que la salida sea siempre completa
        $byHour = array_fill(0, 24, 0);
        $byDayOfWeek = array_fill(0, 7, 0); // 0=Dom, 6=Sáb
        $byDayHour = [];

        foreach ($opens as $click) {
            $ts = $click->email_opened_at;
            if (!$ts) continue;

            // Carbon ya convierte si la zona horaria está configurada bien.
            // setTimezone() asegura que la conversión sea explícita.
            $local = $ts->copy()->setTimezone($appTz);

            $hour = (int) $local->format('G');
            $dow = (int) $local->format('w');

            $byHour[$hour]++;
            $byDayOfWeek[$dow]++;

            $key = $dow . '-' . $hour;
            $byDayHour[$key] = ($byDayHour[$key] ?? 0) + 1;
        }

        // Mejores 3 horas
        $topHours = $byHour;
        arsort($topHours);
        $topHours = array_slice($topHours, 0, 3, true);

        // Mejores 3 días
        $topDays = $byDayOfWeek;
        arsort($topDays);
        $topDays = array_slice($topDays, 0, 3, true);

        // Mejores 5 combinaciones día+hora
        arsort($byDayHour);
        $topSlots = array_slice($byDayHour, 0, 5, true);

        $dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        return [
            'total_opens' => $opens->count(),
            'timezone' => $appTz,
            'by_hour' => $byHour,
            'by_day_of_week' => array_combine($dayNames, $byDayOfWeek),
            'top_hours' => $this->formatTopHours($topHours),
            'top_days' => $this->formatTopDays($topDays, $dayNames),
            'top_slots' => $this->formatTopSlots($topSlots, $dayNames),
        ];
    }

    private function formatTopHours(array $topHours): array
    {
        $out = [];
        foreach ($topHours as $hour => $count) {
            $out[] = [
                'hour' => sprintf('%02d:00 - %02d:59', $hour, $hour),
                'opens' => $count,
            ];
        }
        return $out;
    }

    private function formatTopDays(array $topDays, array $dayNames): array
    {
        $out = [];
        foreach ($topDays as $dow => $count) {
            $out[] = ['day' => $dayNames[$dow], 'opens' => $count];
        }
        return $out;
    }

    private function formatTopSlots(array $topSlots, array $dayNames): array
    {
        $out = [];
        foreach ($topSlots as $key => $count) {
            [$dow, $hour] = explode('-', $key);
            $out[] = [
                'day' => $dayNames[(int) $dow],
                'hour' => sprintf('%02d:00', (int) $hour),
                'opens' => $count,
            ];
        }
        return $out;
    }

    /**
     * Arma el prompt que se le envía a la IA con los datos crudos.
     */
    private function buildSendTimePrompt(array $stats): string
    {
        $tz = $stats['timezone'];
        $prompt = "Análisis de patrones de apertura de correos electrónicos.\n";
        $prompt .= "Zona horaria de los datos: {$tz}\n";
        $prompt .= "Total de aperturas registradas: {$stats['total_opens']}\n\n";

        $prompt .= "Aperturas por hora del día (formato 24h):\n";
        foreach ($stats['by_hour'] as $hour => $count) {
            if ($count > 0) {
                $prompt .= sprintf("  %02d:00 - %02d:59 → %d aperturas\n", $hour, $hour, $count);
            }
        }

        $prompt .= "\nAperturas por día de la semana:\n";
        foreach ($stats['by_day_of_week'] as $day => $count) {
            $prompt .= "  {$day}: {$count} aperturas\n";
        }

        $prompt .= "\nMejores 5 combinaciones día + hora:\n";
        foreach ($stats['top_slots'] as $slot) {
            $prompt .= "  {$slot['day']} a las {$slot['hour']} → {$slot['opens']} aperturas\n";
        }

        $prompt .= "\nBasándote ESTRICTAMENTE en estos datos:\n";
        $prompt .= "1. Identifica los 2 o 3 mejores horarios para enviar campañas y explica por qué.\n";
        $prompt .= "2. Identifica los peores horarios que conviene evitar.\n";
        $prompt .= "3. Sugiere el día y hora exacta para una próxima campaña.\n";
        $prompt .= "4. Advierte si los datos son escasos o tienen sesgos visibles (ej. concentrados en un solo día).\n";
        $prompt .= "Formato: usa viñetas y sé concreto. No inventes datos que no estén arriba.\n";

        return $prompt;
    }

    /**
     * Cliente genérico para APIs compatibles con OpenAI (LM Studio local, etc.)
     */
    private function createChatCompletion(string $systemPrompt, string $userPrompt, int $maxTokens, float $temperature)
    {
        $baseUrl = rtrim(env('LLM_BASE_URL', 'http://localhost:1234/v1'), '/');
        $apiKey = env('LLM_API_KEY', 'lm-studio'); // LM Studio acepta cualquier valor
        $model = env('LLM_MODEL', 'deepseek-r1-0528-qwen3-8b');

        $endpoint = $baseUrl . '/chat/completions';

        \Log::info('AI Request Details:', [
            'endpoint' => $endpoint,
            'model' => $model,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)->post($endpoint, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt]
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ]);

            \Log::info('AI Response Status:', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                \Log::info('AI Response Data:', ['data' => $data]);
                return $data;
            } else {
                \Log::error('AI Request Failed:', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);
                
                return [
                    'error' => [
                        'message' => 'Error en la respuesta del servidor de IA',
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]
                ];
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('AI Connection Error:', ['message' => $e->getMessage()]);
            return [
                'error' => [
                    'message' => 'No se pudo conectar con LM Studio. Verifica que esté ejecutándose en http://localhost:1234',
                    'type' => 'connection_error'
                ]
            ];
        } catch (\Illuminate\Http\Client\RequestException $e) {
            \Log::error('AI Request Error:', ['message' => $e->getMessage()]);
            return [
                'error' => [
                    'message' => 'Error en la petición a la IA: ' . $e->getMessage(),
                    'type' => 'request_error'
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('AI General Error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return [
                'error' => [
                    'message' => 'Error inesperado: ' . $e->getMessage(),
                    'type' => 'general_error'
                ]
            ];
        }
    }

    /* ====================================================================
     * HELPERS PARA PARSEAR EL OUTPUT DEL SCRIPT generar_emails.py
     *
     * El script imprime líneas de éxito como:
     *   "Email enviado a: usuario@dominio.com - Asunto: ..."
     * Y líneas de error como:
     *   "Error al enviar email a usuario@dominio.com: ..."
     *
     * Antes registrábamos TODOS los destinatarios como enviados en la BD,
     * incluso cuando el SMTP fallaba (return code 0 pero 0 correos enviados).
     * Ahora parseamos para registrar sólo los que realmente salieron.
     * ====================================================================*/

    /**
     * Extrae los emails enviados exitosamente del output del script Python.
     */
    private function parseSuccessfulEmails(string $output): array
    {
        $emails = [];
        // Regex: captura el email después de "Email enviado a:"
        if (preg_match_all('/Email enviado a:\s*([^\s]+@[^\s\-]+)/i', $output, $matches)) {
            foreach ($matches[1] as $email) {
                $email = trim($email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $email;
                }
            }
        }
        return array_values(array_unique($emails));
    }

    /**
     * Extrae los emails que fallaron del output del script Python.
     */
    private function parseFailedEmails(string $output): array
    {
        $emails = [];
        if (preg_match_all('/Error al enviar email a\s+([^\s:]+@[^\s:]+)/i', $output, $matches)) {
            foreach ($matches[1] as $email) {
                $email = trim($email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $email;
                }
            }
        }
        return array_values(array_unique($emails));
    }

    /**
     * Verifica si un email específico aparece como enviado exitosamente.
     * Útil para sendTestEmail donde solo hay un destinatario.
     */
    private function emailWasSentSuccessfully(string $output, string $email): bool
    {
        $successful = $this->parseSuccessfulEmails($output);
        return in_array(strtolower(trim($email)), array_map('strtolower', $successful), true);
    }
}