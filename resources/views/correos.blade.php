<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Correos</title>

    <!-- Bootstrap y CSS personalizados -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/correos.css') }}" rel="stylesheet" />
</head>
<body>
    @include('layouts.navbar')

    <div class="container mt-4 correos-page">
        <h1 class="mb-4 text-center">📬 Correos</h1>

        <!-- Configuración de Imagen -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header fw-bold">Configuración de Imagen</div>
            <div class="card-body">
                <form method="POST" action="{{ route('estadisticas.upload-image') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="imagen" class="form-label">Seleccionar Imagen</label>
                        <input type="file" name="imagen" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Imagen</button>
                </form>
            </div>
        </div>

        <!-- Envío de Correos Masivos -->
        <div class="card shadow-sm">
            <div class="card-header fw-bold">Envío de Correos Masivos</div>
            <div class="card-body">
                <form method="POST" action="{{ route('estadisticas.upload-email-list') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="archivo" class="form-label">Archivo de Correos</label>
                                <input type="file" name="archivo" class="form-control" required>
                                <div class="form-text">Suba un archivo que contenga direcciones de correo. El sistema extraerá las direcciones automáticamente.</div>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <label class="form-label d-block">Imagen Actual:</label>
                            <img src="{{ route('estadisticas.current-image') }}" alt="Imagen actual" class="img-fluid rounded border p-1" style="max-height: 100px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="asunto" class="form-label">Asunto del Correo</label>
                        <input type="text" name="asunto" class="form-control" placeholder="Ingrese el asunto del correo" required>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="4" placeholder="Ingrese una descripción para el correo" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Enviar Correos
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
