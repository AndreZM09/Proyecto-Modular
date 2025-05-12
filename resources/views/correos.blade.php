@extends('layouts.app')

@section('content')
<div class="container mt-0 pt-3">
    <h1 class="mb-4 d-flex align-items-center">
        <img src="{{ asset('imagenes/mail.png') }}" alt="" class="img-icon me-2" style="width: 40px; height: auto;">
        Correos
    </h1>
    
    <!-- Formulario para subir imagen -->
    <div class="card mb-4">
        <div class="card-header">Configuración de Imagen</div>
        <div class="card-body">
            <form id="imageUploadForm" action="{{ route('estadisticas.upload-image') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="image" class="form-label">Seleccionar Imagen</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Imagen</button>
            </form>
        </div>
    </div>
    
    <!-- Formulario para envío masivo de correos -->
    <div class="card">
        <div class="card-header">Envío de Correos Masivos</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <form id="bulkEmailForm" action="{{ route('estadisticas.upload-email-list') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="emailFile" class="form-label">Archivo de Correos</label>
                            <input type="file" class="form-control" id="emailFile" name="emailFile" required>
                            <div class="form-text">Suba un archivo que contenga direcciones de correo. El sistema extraerá las direcciones automáticamente.</div>
                        </div>
                        <div class="mb-3">
                            <label for="bulkSubject" class="form-label">Asunto del Correo</label>
                            <input type="text" class="form-control" id="bulkSubject" name="subject" placeholder="Ingrese el asunto del correo">
                        </div>
                        <div class="mb-3">
                            <label for="bulkDescription" class="form-label">Descripción</label>
                            <textarea class="form-control" id="bulkDescription" name="description" rows="3" placeholder="Ingrese una descripción para el correo"></textarea>
                        </div>
                        <button type="button" id="sendBulkEmails" class="btn btn-primary">
                            <i class="bi bi-send"></i> Enviar Correos
                        </button>
                        <div id="bulkEmailStatus" class="mt-2" style="display: none;">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Enviando...</span>
                            </div>
                            <span class="ms-2">Procesando archivo y enviando correos...</span>
                        </div>
                    </form>
                </div>
                <div class="col-md-6">
                    <h5>Imagen Actual:</h5>
                    <div class="image-container">
                        @if($currentImage)
                            <img src="{{ asset('storage/email_images/' . $currentImage->filename) }}" 
                                 alt="Imagen actual" 
                                 class="img-thumbnail" 
                                 style="max-height: 200px;">
                        @else
                            <p class="text-muted">No hay imagen configurada</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Añadir código para manejar la subida de imágenes
    document.addEventListener('DOMContentLoaded', function() {
        const imageForm = document.getElementById('imageUploadForm');
        if (imageForm) {
            imageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Imagen guardada exitosamente');
                        location.reload();
                    } else {
                        alert('Error al guardar la imagen: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al guardar la imagen: ' + error.message);
                });
            });
        }
        
        // Código para enviar correos masivos
        const sendBulkButton = document.getElementById('sendBulkEmails');
        if (sendBulkButton) {
            sendBulkButton.addEventListener('click', function() {
                const fileInput = document.getElementById('emailFile');
                const subjectInput = document.getElementById('bulkSubject');
                const descriptionInput = document.getElementById('bulkDescription');
                const button = this;
                const statusDiv = document.getElementById('bulkEmailStatus');
                const statusText = statusDiv.querySelector('span');
                
                // Verificar que se haya seleccionado un archivo
                if (!fileInput.files || fileInput.files.length === 0) {
                    alert('Por favor, seleccione un archivo que contenga correos electrónicos');
                    return;
                }
                
                // Crear FormData y agregar campos
                const formData = new FormData();
                formData.append('emailFile', fileInput.files[0]);
                formData.append('subject', subjectInput.value || 'Correo importante');
                formData.append('description', descriptionInput.value || 'Información importante para ti');
                
                // Mostrar indicador de carga y deshabilitar botón
                button.disabled = true;
                statusDiv.style.display = 'block';
                statusText.textContent = 'Procesando archivo y enviando correos...';
                
                // Obtener token CSRF
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Enviar solicitud
                fetch('{{ route("estadisticas.upload-email-list") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => {
                    return response.json().then(data => {
                        data.httpStatus = response.status;
                        return data;
                    });
                })
                .then(data => {
                    // Restaurar el botón y ocultar el spinner
                    button.disabled = false;
                    statusDiv.style.display = 'none';
                    
                    if (data.success) {
                        // Mensaje de éxito
                        alert('Correos enviados exitosamente');
                        // Limpiar el formulario
                        fileInput.value = '';
                        subjectInput.value = '';
                        descriptionInput.value = '';
                    } else {
                        // Mensaje de error
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    // Restaurar el botón y ocultar el spinner
                    button.disabled = false;
                    statusDiv.style.display = 'none';
                    
                    // Mensaje de error
                    alert('Error al enviar los correos: ' + error.message);
                });
            });
        }
    });
</script>
@endsection 