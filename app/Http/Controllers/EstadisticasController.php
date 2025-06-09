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
        try {
            // Validar la solicitud
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,bmp,tiff,svg',
                'subject' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'priority' => 'nullable|in:normal,high,urgent'
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
                    'priority' => $request->input('priority', 'normal')
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

    public function trackClick(Request $request)
    {
        $email = $request->query('email');
        $ip = $request->ip();
        $imageId = $request->query('img_id'); // Obtener el ID de la imagen

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
            
            // Guardar el email en un archivo temporal con codificación UTF-8 sin BOM
            $emailListPath = storage_path('app/email_list_temp.txt');
            file_put_contents($emailListPath, $email, LOCK_EX);
            
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
            $command = 'python ' . base_path() . '/resources/python/generar_emails.py 2>&1';
            exec($command, $output, $returnCode);
            
            // Limpiar archivo temporal
            @unlink($emailListPath);
            
            if ($returnCode !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar los correos: ' . implode("\n", $output)
                ], 500);
            }
            
            // Registrar envíos en BD
            $now = now();
            $maxIdPerson = Click::where('id_img', $image->id)->max('id_person') ?? 0;
            
            foreach ($emailData as $index => $emailInfo) {
                Click::create([
                    'email' => $emailInfo['email'],
                    'id_img' => $image->id,
                    'id_person' => $maxIdPerson + $index + 1,
                    'email_sent_at' => $now
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Campaña masiva enviada exitosamente a ' . count($validEmails) . ' correos',
                'total' => count($validEmails)
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
            $command = 'python ' . base_path() . '/resources/python/generar_emails.py 2>&1';
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
            
            // Registrar los envíos en la tabla clicks
            $now = now();
            
            // Determinar el siguiente id_person para esta imagen
            $maxIdPerson = Click::where('id_img', $image->id)->max('id_person') ?? 0;
            
            foreach ($emailData as $index => $emailInfo) {
                Click::create([
                    'email' => $emailInfo['email'],
                    'id_img' => $image->id,
                    'id_person' => $maxIdPerson + $index + 1,
                    'email_sent_at' => $now
                ]);
            }
            
            // Respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Correos enviados exitosamente',
                'total' => count($emailData)
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
}