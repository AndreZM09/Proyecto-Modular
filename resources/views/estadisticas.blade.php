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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            padding-top: 70px !important; /* Espacio para la barra de navegación fija */
        }
        .campaign-card {
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .campaign-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .campaign-image {
            height: 120px;
            object-fit: contain;
            margin: 0 auto;
            display: block;
            border-radius: 8px;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
    <style>
        /* CSS del chatbot movido a la vista de campañas para que sea más
           accesible desde la lista general. */
    </style>
</head>
    
<body class="estadisticas-page">
    @include('layouts.navbar')

    <!-- Contenido existente de la página -->
    <div class="container mt-4">
        @if(isset($campaignTitle))
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-bar-chart-line"></i>
                    Estadísticas: {{ $campaignTitle }}
                </h1>
                <a href="{{ route('campañas') }}" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Volver a Campañas
                </a>
            </div>
        @else
            <h1 class="text-center">📊 Estadísticas</h1>
        @endif
        
        <p class="text-center">Total de correos enviados: <span class="badge bg-primary">{{ $emailsSent }}</span></p>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="chart-container">
                    <h3>Correos Abiertos</h3>
                    <div class="chart-wrapper">
                        <div class="chart-center-text">
                            <span class="chart-center-main">{{ $opened }}</span>
                            <span class="chart-center-label">Abiertos</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="chart-container">
                    <h3>Correos Enviados</h3>
                    <div class="chart-wrapper">
                        <canvas id="sentChart"></canvas>
                        <div class="chart-center-text">
                            <span class="chart-center-main">{{ $emailsSent }}</span>
                            <span class="chart-center-label">Enviados</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="chart-container">
                    <h3>Clics en Imagen</h3>
                    <div class="chart-wrapper">
                        <canvas id="clickedChart"></canvas>
                        <div class="chart-center-text">
                            <span class="chart-center-main">{{ $clicksCount }}</span>
                            <span class="chart-center-label">Clics</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas por Campaña -->
        @if(!isset($campaign))
        <h2 class="mt-5 mb-4">
            <i class="bi bi-bar-chart-line"></i>
            Estadísticas por Campaña
        </h2>
        @endif
        
        @if(count($campaignStats) > 0)
            <div class="row">
                @foreach($campaignStats as $campaign)
                <div class="col-md-{{ isset($campaignTitle) ? '12' : '4' }}">
                    <div class="card campaign-card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $campaign['name'] }}</h5>
                            <p class="text-muted small">{{ $campaign['date'] }}</p>
                            
                            <div class="row align-items-center">
                                @if(isset($campaign['image']))
                                <div class="{{ isset($campaignTitle) ? 'col-md-3' : 'col-12' }}">
                                    <img src="{{ asset('storage/email_images/' . $campaign['image']) }}" 
                                         class="campaign-image my-3" alt="Imagen de campaña">
                                </div>
                                @endif
                                
                                <div class="{{ isset($campaignTitle) ? 'col-md-9' : 'col-12' }}">
                                    <div class="row text-center mt-3">
                                        <div class="col-4">
                                            <div class="stat-value text-primary">{{ $campaign['sent'] }}</div>
                                            <div class="stat-label">Enviados</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="stat-value text-success">{{ $campaign['clicks'] }}</div>
                                            <div class="stat-label">Clics</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="stat-value text-info">{{ $campaign['open_rate'] }}%</div>
                                            <div class="stat-label">Tasa</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                No hay datos de campañas disponibles.
            </div>
        @endif
    </div>

    <!-- Sección de detalles de la campaña -->
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Detalles de la Campaña</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Imagen de la campaña -->
                    <div class="col-md-4">
                        @if(isset($currentImage) || isset($campaign))
                            @php
                                $imageDetails = isset($campaign) ? $campaign : $currentImage;
                                $filename = is_array($imageDetails) ? $imageDetails['image'] : $imageDetails->filename;
                            @endphp
                            <img src="{{ asset('storage/email_images/' . $filename) }}" 
                                 alt="Imagen de la campaña" 
                                 class="img-fluid rounded">
                        @else
                            <div class="alert alert-info">
                                No hay imagen configurada para esta campaña
                            </div>
                        @endif
                    </div>
                    <!-- Detalles de la campaña -->
                    <div class="col-md-8">
                        @if(isset($currentImage) || isset($campaign))
                            @php
                                $imageDetails = isset($campaign) ? $campaign : $currentImage;
                                $subject = is_array($imageDetails) ? ($imageDetails['name'] ?? 'Sin asunto') : ($imageDetails->subject ?: 'Sin asunto');
                                $created_at = is_array($imageDetails) ? ($imageDetails['date'] ?? 'Fecha no disponible') : $imageDetails->created_at->format('d/m/Y H:i');
                                $description = is_array($imageDetails) ? ($imageDetails['description'] ?? 'Sin descripción') : ($imageDetails->description ?: 'Sin descripción');
                                $priority = is_array($imageDetails) ? ($imageDetails['priority'] ?? 'normal') : ($imageDetails->priority ?: 'normal');
                            @endphp
                            <h4>{{ $subject }}</h4>
                            <p class="text-muted mb-2">
                                <strong>Fecha de creación:</strong> 
                                {{ $created_at }}
                            </p>
                            <p class="mb-2">
                                <strong>Descripción:</strong><br>
                                {{ $description }}
                            </p>
                            <p class="mb-2">
                                <strong>Prioridad:</strong>
                                <span class="badge {{ $priority === 'high' ? 'bg-danger' : ($priority === 'urgent' ? 'bg-warning' : 'bg-success') }}">
                                    {{ ucfirst($priority) }}
                                </span>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de correos enviados -->
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lista de Correos Enviados</h5>
                <span class="badge bg-light text-dark">Total: {{ count($emailList) }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Correo</th>
                                <th>Fecha de Envío</th>
                                <th>Estado</th>
                                <th>Última Interacción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($emailList as $email)
                                <tr>
                                    <td>{{ $email->email }}</td>
                                    <td>{{ $email->email_sent_at ? $email->email_sent_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td>
                                        @if($email->clicked_at)
                                            <span class="badge bg-success">Clic realizado</span>
                                        @else
                                            <span class="badge bg-secondary">Sin interacción</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($email->clicked_at)
                                            Clic realizado ({{ $email->clicked_at->format('d/m/Y H:i') }})
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No hay correos enviados para esta campaña</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Chatbot movido a campañas.blade.php para mejor accesibilidad. --}}

    <script>
        // Lógica de los gráficos (ya existente)
        const commonChartOptions = {
            cutout: '75%',
            plugins: { legend: { display: true, position: 'bottom' } }
        };

        new Chart(document.getElementById('sentChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Enviados', 'No Enviados'],
                datasets: [{
                    data: [{{ $emailsSent }}, {{ max(0, 100 - $emailsSent) }}],
                    backgroundColor: ['#28a745', '#f0f0f0']
                }]
            },
            options: commonChartOptions
        });

        new Chart(document.getElementById('clickedChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Con Clic', 'Sin Clic'],
                datasets: [{
                    data: [{{ $clicksCount }}, {{ ($emailsSent > $clicksCount) ? ($emailsSent - $clicksCount) : 0 }}],
                    backgroundColor: ['#007bff', '#f0f0f0']
                }]
            },
            options: commonChartOptions
        });

        // Lógica del chatbot movida a campañas.blade.php
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    @include('partials.chatbot')
</body>
</html>