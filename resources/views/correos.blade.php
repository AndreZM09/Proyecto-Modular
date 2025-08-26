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
