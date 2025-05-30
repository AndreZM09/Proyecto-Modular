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
</head>

<body class="estadisticas-page">
    @include('layouts.navbar')

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
                    <h3>Correos Enviados</h3>
                    <div class="chart-wrapper">
                        <div class="chart-center-text">
                            <span class="chart-center-main">{{ $emailsSent }}</span>
                            <span class="chart-center-label">Enviados</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="chart-container">
                    <h3>Correos Abiertos</h3>
                    <div class="chart-wrapper">
                        <canvas id="openedChart"></canvas>
                        <div class="chart-center-text">
                            <span class="chart-center-main">{{ $opened }}</span>
                            <span class="chart-center-label">Abiertos</span>
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
                                @if($campaign['image'])
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

    <script>
        // Configuración común para gráficas
        const commonChartOptions = {
            cutout: '75%',
            plugins: { legend: { display: true, position: 'bottom' } }
        };

        // Gráfica de emails abiertos
        new Chart(document.getElementById('openedChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Abiertos', 'No Abiertos'],
                datasets: [{
                    data: [{{ $opened }}, {{ ($emailsSent > $opened) ? ($emailsSent - $opened) : 0 }}],
                    backgroundColor: ['#28a745', '#f0f0f0']
                }]
            },
            options: commonChartOptions
        });

        // Gráfica de clics realizados
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
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>