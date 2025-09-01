<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>📬 Correos - Campañas de Marketing UDG</title>

    <!-- Bootstrap y CSS personalizados -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/correos.css') }}" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            padding-top: 70px !important; /* Espacio para la barra de navegación fija */
        }
    </style>
</head>
<body>
    @include('layouts.navbar')

    <div class="container correos-page">
        <h1 class="mb-5">
            <i class="bi bi-envelope-heart"></i>
            Gestión de Correos
        </h1>

        <!-- Asistente de IA para Imágenes de Correo -->
        <div class="card mb-4" style="border: 2px solid #6366f1; box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);">
            <div class="card-header" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border-bottom: 2px solid #6366f1;">
                <i class="bi bi-robot" style="font-size: 1.2em; margin-right: 8px;"></i>
                <strong>🤖 Asistente de IA para Mejorar tu Contenido</strong>
            </div>
            <div class="card-body" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                <div class="alert alert-info border-0 mb-4" style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border: 1px solid #3b82f6; border-radius: 12px;">
                    <i class="bi bi-lightbulb" style="color: #1d4ed8;"></i>
                    <strong style="color: #1e40af;">¿Necesitas ayuda para crear imágenes de correo más efectivas?</strong> 
                    <span style="color: #1e40af;">Nuestro asistente de IA analiza tu historial de campañas y te proporciona recomendaciones personalizadas para mejorar el engagement.</span>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <button type="button" id="getAIRecommendationsBtn" class="btn btn-lg w-100" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none; color: white; padding: 15px; font-weight: 600; border-radius: 12px; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);">
                                <i class="bi bi-magic" style="font-size: 1.2em; margin-right: 8px;"></i>
                                Obtener Recomendaciones de IA
                            </button>
                            <div class="form-text text-center mt-2" style="color: #6366f1; font-weight: 500;">
                                <i class="bi bi-info-circle"></i>
                                Análisis basado en tu historial de campañas
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <button type="button" id="openAIChatBtn" class="btn btn-outline-primary btn-lg w-100" style="border: 2px solid #6366f1; color: #6366f1; background: white; padding: 15px; font-weight: 600; border-radius: 12px; transition: all 0.3s ease;">
                                <i class="bi bi-chat-dots" style="font-size: 1.2em; margin-right: 8px;"></i>
                                Hacer Pregunta Específica
                            </button>
                            <div class="form-text text-center mt-2" style="color: #6366f1; font-weight: 500;">
                                <i class="bi bi-info-circle"></i>
                                Consulta personalizada sobre tu contenido
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenedor de recomendaciones de IA -->
                <div id="aiRecommendationsContainer" class="mt-4" style="display: none;">
                    <div class="card" style="border: 2px solid #10b981; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                        <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-bottom: 2px solid #10b981;">
                            <i class="bi bi-stars" style="margin-right: 8px;"></i>
                            <strong>✨ Recomendaciones de IA para tu Contenido</strong>
                        </div>
                        <div class="card-body">
                            <div id="aiRecommendationsContent"></div>
                        </div>
                    </div>
                </div>

                <!-- Chat de IA -->
                <div id="aiChatContainer" class="mt-4" style="display: none;">
                    <div class="card" style="border: 2px solid #f59e0b; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border-bottom: 2px solid #f59e0b;">
                            <span><i class="bi bi-chat-dots" style="margin-right: 8px;"></i> <strong>💬 Asistente de IA</strong></span>
                            <button type="button" id="closeAIChatBtn" class="btn btn-sm" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="aiChatMessages" class="mb-3" style="max-height: 300px; overflow-y: auto; border: 2px solid #fbbf24; border-radius: 12px; padding: 15px; background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);">
                                <div class="text-center text-muted">
                                    <i class="bi bi-robot" style="font-size: 2rem; color: #f59e0b;"></i>
                                    <p class="mt-2 mb-0" style="color: #92400e; font-weight: 500;">¡Hola! Soy tu asistente de IA para marketing de correo. ¿En qué puedo ayudarte hoy?</p>
                                </div>
                            </div>
                            <div class="input-group">
                                <input type="text" id="aiQuestionInput" class="form-control" placeholder="Escribe tu pregunta sobre el contenido de la imagen..." style="border: 2px solid #fbbf24; border-radius: 8px;">
                                <button type="button" id="askAIQuestionBtn" class="btn" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; color: white; border-radius: 8px; font-weight: 600;">
                                    <i class="bi bi-send"></i>
                                    Enviar
                                </button>
                            </div>
                            <div class="form-text mt-2" style="color: #92400e;">
                                <i class="bi bi-info-circle"></i>
                                Ejemplos: "¿Qué colores usar para una campaña de descuento?", "¿Cómo mejorar el call-to-action?"
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Indicador de carga -->
                <div id="aiLoadingIndicator" class="text-center mt-3" style="display: none;">
                    <div class="spinner-border" role="status" style="color: #6366f1; width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2" style="color: #6366f1; font-weight: 500;">La IA está analizando tu contenido...</p>
                </div>
            </div>
        </div>

        <!-- Configuración de Imagen -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-image"></i>
                Configuración de Imagen
            </div>
            <div class="card-body">
                <form id="imageForm" method="POST" action="{{ route('estadisticas.upload-image') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="image" class="form-label">
                                    <i class="bi bi-cloud-upload"></i>
                                    Seleccionar Imagen
                                </label>
                                <input type="file" name="image" id="image" class="form-control" accept="image/*" required>
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i>
                                    Formatos soportados: JPG, PNG, GIF, WEBP, BMP, TIFF, SVG. Sin límite de tamaño.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="link_redirection" class="form-label">
                                    <i class="bi bi-link"></i>
                                    URL de Redirección (opcional)
                                </label>
                                <input type="url" name="link_redirection" id="link_redirection" class="form-control" placeholder="https://ejemplo.com">
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i>
                                    Si se proporciona, la imagen será un enlace a esta URL.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="imagen-actual-container">
                                <label class="form-label d-block">
                                    <i class="bi bi-eye"></i>
                                    Vista Previa
                                </label>
                                <div id="imagePreview" style="min-height: 120px; display: flex; align-items: center; justify-content: center;">
                                    <div class="text-muted">
                                        <i class="bi bi-image" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">Ninguna imagen seleccionada</p>
                                    </div>
                                </div>
                                <div id="clearImageContainer" class="mt-2 text-center" style="display: none;">
                                    <button type="button" id="clearImage" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Eliminar selección
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i>
                        Guardar Imagen
                    </button>
                </form>
            </div>
        </div>

        <!-- Envío Personalizado -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-send-plus"></i>
                Envío Personalizado
            </div>
            <div class="card-body">
                <form id="emailFormPersonalizado" method="POST" action="{{ route('estadisticas.upload-email-list') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="alert alert-success border-0 mb-4" style="background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%); border-radius: 12px;">
                        <i class="bi bi-person-check"></i>
                        <strong>Envío Personalizado:</strong> Sube un archivo Excel con correos, asuntos, mensajes y prioridades individuales para cada destinatario.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="emailFilePersonalizado" class="form-label">
                                    <i class="bi bi-file-earmark-excel"></i>
                                    Archivo Excel Personalizado
                                </label>
                                <input type="file" name="emailFile" id="emailFilePersonalizado" class="form-control" accept=".xlsx,.xls,.csv" required>
                                <div class="form-text">
                                    <i class="bi bi-lightbulb"></i>
                                    Debe contener: correo, asunto, mensaje, prioridad
                                </div>
                                <div class="mt-2">
                                    <a href="{{ asset('ejemplos/formato_ejemplo.csv') }}" download class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-download"></i> Descargar formato de ejemplo (CSV)
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="imagen-actual-container">
                                <label class="form-label d-block">
                                    <i class="bi bi-image-alt"></i>
                                    Imagen Actual de Campaña
                                </label>
                                @if($currentImage ?? null)
                                    <a href="{{ route('clicks.track', ['id_img' => $currentImage->id, 'email' => 'RECIPIENT_EMAIL']) }}" target="_blank">
                                        <img src="{{ asset('storage/email_images/' . $currentImage->filename) }}"
                                             alt="Imagen actual" class="img-fluid">
                                    </a>
                                    <p class="mt-2 mb-0 text-muted small">{{ $currentImage->subject ?? 'Sin título' }}</p>
                                @else
                                    <div class="text-muted">
                                        <i class="bi bi-exclamation-triangle" style="font-size: 2rem; color: #ffc107;"></i>
                                        <p class="mt-2 mb-0">No hay imagen configurada</p>
                                        <small>Configure una imagen primero</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary" id="submitBtnPersonalizado" disabled>
                            <i class="bi bi-rocket-takeoff"></i>
                            Enviar Campaña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Vista previa de imagen
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            const clearContainer = document.getElementById('clearImageContainer');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="img-fluid" style="max-height: 120px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">`;
                    clearContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                resetImagePreview();
            }
        });

        // Función para resetear la vista previa de imagen
        function resetImagePreview() {
            const preview = document.getElementById('imagePreview');
            const clearContainer = document.getElementById('clearImageContainer');
            
            preview.innerHTML = `
                <div class="text-muted">
                    <i class="bi bi-image" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mt-2 mb-0">Ninguna imagen seleccionada</p>
                </div>
            `;
            clearContainer.style.display = 'none';
        }

        // Botón para limpiar la imagen seleccionada
        document.getElementById('clearImage').addEventListener('click', function() {
            document.getElementById('image').value = '';
            resetImagePreview();
        });

        // Configuración CSRF para AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Manejo AJAX del formulario de imagen
        document.getElementById('imageForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevenir envío normal del formulario
            
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            
            // Mostrar estado de carga
            btn.classList.add('loading');
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
            btn.disabled = true;
            
            // Crear FormData para envío de archivos
            const formData = new FormData(this);
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Mostrar mensaje de éxito
                    showMessage('success', response.message || 'Imagen guardada exitosamente');
                    
                    // Resetear formulario
                    document.getElementById('imageForm').reset();
                    document.getElementById('imagePreview').innerHTML = `
                        <div class="text-muted">
                            <i class="bi bi-image" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-2 mb-0">Ninguna imagen seleccionada</p>
                        </div>
                    `;
                    
                    // Recargar la página después de 1.5 segundos para mostrar la nueva imagen
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    let errorMessage = 'Error al guardar la imagen';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showMessage('error', errorMessage);
                },
                complete: function() {
                    // Restaurar botón
                    btn.classList.remove('loading');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            });
        });

        // (Se eliminó la lógica de Envío Masivo)

        // Manejar la previsualización del archivo Excel PERSONALIZADO
        document.getElementById('emailFilePersonalizado').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('emailFile', file);

            // Mostrar indicador de carga
            $('#previewInfoPersonalizado').html('<div class="text-center"><i class="bi bi-hourglass-split"></i> Procesando archivo...</div>').show();
            
            // Deshabilitar botón mientras se procesa
            $('#submitBtnPersonalizado').prop('disabled', true);

            // Enviar archivo para previsualización
            $.ajax({
                url: '{{ route("estadisticas.preview-excel") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Habilitar botón de envío
                        $('#submitBtnPersonalizado').prop('disabled', response.data.totalEmails === 0);
                        
                        // Mostrar información del archivo
                        $('#totalEmailsPersonalizado').text(response.data.totalEmails);
                        $('#previewInfoPersonalizado').html(`
                            <div class="alert alert-info">
                                <h6><i class="bi bi-info-circle"></i> Información del archivo</h6>
                                <p><strong>Total de correos:</strong> ${response.data.totalEmails}</p>
                            </div>
                        `).show();
                        
                        showMessage('success', `Archivo personalizado cargado correctamente. ${response.data.totalEmails} correos encontrados.`);
                    } else {
                        $('#previewInfoPersonalizado').hide();
                        $('#submitBtnPersonalizado').prop('disabled', true);
                        showMessage('error', response.message || 'Error al cargar el archivo');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error al cargar el archivo';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    $('#previewInfoPersonalizado').hide();
                    $('#submitBtnPersonalizado').prop('disabled', true);
                    showMessage('error', errorMessage);
                }
            });
        });

        // Manejo del formulario de ENVÍO PERSONALIZADO
        document.getElementById('emailFormPersonalizado').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevenir envío normal del formulario
            
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            
            // Mostrar estado de carga
            btn.classList.add('loading');
            btn.innerHTML = '<i class="bi bi-rocket-takeoff"></i> Enviando...';
            btn.disabled = true;
            
            // Crear FormData para envío de archivos
            const formData = new FormData(this);
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Mostrar mensaje de éxito
                    showMessage('success', response.message || 'Campaña personalizada enviada exitosamente');
                    
                    // Resetear formulario
                    document.getElementById('emailFormPersonalizado').reset();
                    $('#previewInfoPersonalizado').hide();
                    
                    // Opcional: recargar después de 2 segundos para refrescar datos
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                },
                error: function(xhr) {
                    let errorMessage = 'Error al enviar la campaña personalizada';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        // Manejar errores de validación
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    }
                    showMessage('error', errorMessage);
                },
                complete: function() {
                    // Restaurar botón
                    btn.classList.remove('loading');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            });
        });

        // Función para mostrar mensajes
        function showMessage(type, message) {
            // Remover mensajes anteriores
            $('.alert-message').remove();
            
            let alertClass, icon;
            if (type === 'success') {
                alertClass = 'alert-success';
                icon = 'bi-check-circle';
            } else if (type === 'info') {
                alertClass = 'alert-info';
                icon = 'bi-info-circle';
            } else {
                alertClass = 'alert-danger';
                icon = 'bi-exclamation-triangle';
            }
            
            const messageHtml = `
                <div class="alert ${alertClass} alert-message alert-dismissible fade show" role="alert" style="margin-bottom: 1rem; border-radius: 12px;">
                    <i class="bi ${icon}"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Insertar el mensaje antes del primer card
            $('.card').first().before(messageHtml);
            
            // Auto-hide después de 5 segundos
            setTimeout(function() {
                $('.alert-message').fadeOut();
            }, 5000);
        }
    </script>
</body>
</html>
