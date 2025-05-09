<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Estadísticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/estadisticas.css') }}" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Añadir Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="estadisticas-page">
    @include('layouts.navbar')

    <div class="container mt-4">
        <h1 class="text-center">📊 Estadísticas</h1>
        
        <!-- Sección de configuración de imagen -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Configuración de Imagen para Correos</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <form id="imageUploadForm" action="{{ route('estadisticas.upload-image') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="image" class="form-label">Seleccionar Imagen</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Subir Imagen</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <div id="currentImageContainer">
                            @if($currentImage)
                                <h6>Imagen Actual:</h6>
                                <img src="{{ asset('storage/email_images/' . $currentImage->filename) }}" 
                                     alt="Imagen actual" 
                                     class="img-thumbnail" 
                                     style="max-height: 200px;">
                                <div class="mt-2">
                                    <button id="sendTestEmail" class="btn btn-success">
                                        <i class="bi bi-envelope"></i> Enviar Correo
                                    </button>
                                    <div id="emailStatus" class="mt-2" style="display: none;">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Enviando...</span>
                                        </div>
                                        <span class="ms-2">Enviando correos...</span>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">No hay imagen configurada</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-center">Total de registros en la tabla <strong>clicks</strong>: <span class="badge bg-primary">{{ $clicksCount }}</span></p>

        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <h3>Emails Abiertos vs No Abiertos</h3>
                    <div class="chart-wrapper double-value">
                        <canvas id="openedChart"></canvas>
                        <div class="chart-center-text">
                            <span class="chart-center-main">{{ $opened }}</span>
                            <span class="chart-center-label">Abiertos</span>
                            <span class="chart-center-secondary">{{ $notOpened }}</span>
                            <span class="chart-center-label">No Abiertos</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h3>Clics por Municipio</h3>
                    <div class="chart-wrapper municipio-chart">
                        <canvas id="municipioChart"></canvas>
                        <div class="chart-center-text">
                            <span class="chart-center-main">{{ $totalClicsMunicipios }}</span>
                            <span class="chart-center-label">Total</span>
                        </div>
                    </div>
                    <div class="municipio-legend">
                        @foreach($clicksByMunicipio as $municipio)
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: {{ $municipioColors[$municipio->municipio] }}"></span>
                            <span class="legend-label">{{ $municipio->municipio }}: {{ $municipio->total }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="chart-container">
                    <h3>Personas que hicieron clic</h3>
                    <div class="chart-wrapper">
                        <canvas id="clickedChart"></canvas>
                        <div class="chart-center-text">
                            <span class="chart-center-main">{{ $clicksCount }}</span>
                            <span class="chart-center-label">Personas</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-container">
                    <h3>Personas que no hicieron clic</h3>
                    <div class="chart-wrapper">
                        <canvas id="notClickedChart"></canvas>
                        <div class="chart-center-text">
                            <span class="chart-center-main">{{ $totalEmails - $clicksCount }}</span>
                            <span class="chart-center-label">Personas</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-container">
                    <h3>Personas que recibieron el mail</h3>
                    <div class="chart-wrapper">
                        <canvas id="receivedChart"></canvas>
                        <div class="chart-center-text">
                            <span class="chart-center-main">{{ $totalEmails }}</span>
                            <span class="chart-center-label">Personas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuración común para todas las gráficas
        const commonChartOptions = {
            cutout: '75%',
            plugins: {
                legend: {
                    display: false
                }
            },
            layout: {
                padding: {
                    top: 20,
                    bottom: 20
                }
            }
        };

        // Gráfica de emails abiertos (mostrando ambos valores)
        const openedCtx = document.getElementById('openedChart').getContext('2d');
        new Chart(openedCtx, {
            type: 'doughnut',
            data: {
                labels: ['Abiertos', 'No Abiertos'],
                datasets: [{
                    data: [{{ $opened }}, {{ $notOpened }}],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                ...commonChartOptions,
                plugins: {
                    tooltip: {
                        enabled: true
                    }
                }
            }
        });

        // Gráfica de clics por municipio
        const municipioCtx = document.getElementById('municipioChart').getContext('2d');
        new Chart(municipioCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($clicksByMunicipio->pluck('municipio')) !!},
                datasets: [{
                    data: {!! json_encode($clicksByMunicipio->pluck('total')) !!},
                    backgroundColor: {!! json_encode(array_values($municipioColors)) !!},
                    borderWidth: 0
                }]
            },
            options: {
                ...commonChartOptions,
                cutout: '65%',
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw} clics`;
                            }
                        }
                    }
                }
            }
        });

        // Configuración para gráficas de medidores (180 grados)
        const gaugeOptions = {
            ...commonChartOptions,
            rotation: -90,
            circumference: 180,
            plugins: {
                tooltip: {
                    enabled: false
                }
            }
        };

        // Gráfica de personas que hicieron clic
        const clickedCtx = document.getElementById('clickedChart').getContext('2d');
        const clickedChart = new Chart(clickedCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [{{ $clicksCount }}, {{ $clicksCount * 0.3 }}],
                    backgroundColor: ['#28a745', '#f0f0f0'],
                    borderWidth: 0
                }]
            },
            options: gaugeOptions
        });

        // Gráfica de personas que no hicieron clic
        const notClickedCtx = document.getElementById('notClickedChart').getContext('2d');
        const notClickedChart = new Chart(notClickedCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [{{ $totalEmails - $clicksCount }}, {{ ($totalEmails - $clicksCount) * 0.3 }}],
                    backgroundColor: ['#dc3545', '#f0f0f0'],
                    borderWidth: 0
                }]
            },
            options: gaugeOptions
        });

        // Gráfica de personas que recibieron el mail
        const receivedCtx = document.getElementById('receivedChart').getContext('2d');
        const receivedChart = new Chart(receivedCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [{{ $totalEmails }}, {{ $totalEmails * 0.3 }}],
                    backgroundColor: ['#ffc107', '#f0f0f0'],
                    borderWidth: 0
                }]
            },
            options: gaugeOptions
        });

        // Añadir código para manejar la subida de imágenes
        document.getElementById('imageUploadForm').addEventListener('submit', function(e) {
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
                    alert('Imagen subida exitosamente');
                    location.reload();
                } else {
                    alert('Error al subir la imagen: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al subir la imagen: ' + error.message);
            });
        });

        // Añadir código para enviar el correo de prueba
        document.getElementById('sendTestEmail')?.addEventListener('click', function() {
            const button = this;
            const statusDiv = document.getElementById('emailStatus');
            
            // Deshabilitar el botón y mostrar el indicador de carga
            button.disabled = true;
            statusDiv.style.display = 'block';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('{{ route("estadisticas.send-test-email") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
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
                    alert('Correo de prueba enviado exitosamente');
                } else {
                    alert('Error al enviar el correo de prueba: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al enviar el correo de prueba: ' + error.message);
            })
            .finally(() => {
                // Rehabilitar el botón y ocultar el indicador de carga
                button.disabled = false;
                statusDiv.style.display = 'none';
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>