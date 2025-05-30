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
    <style>
        body {
            padding-top: 70px !important; /* Espacio para la barra de navegación fija */
        }
    </style>
</head>

<body class="estadisticas-page">
    @include('layouts.navbar')

    <div class="container mt-4">
        <h1 class="text-center">📊 Estadísticas</h1>
        
        <p class="text-center">Total de clics registrados: <span class="badge bg-primary">{{ $clicksCount }}</span></p>

        @if($clicksCount > 0)
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="chart-container">
                    <h3>Emails Abiertos</h3>
                    <div class="chart-wrapper">
                        <canvas id="openedChart"></canvas>
                        <div class="chart-center-text">
                            <span class="chart-center-main">{{ $opened }}</span>
                            <span class="chart-center-label">Abiertos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 offset-md-3">
                <div class="chart-container">
                    <h3>Clics realizados</h3>
                    <div class="chart-wrapper">
                        <canvas id="clickedChart"></canvas>
                        <div class="chart-center-text">
                            <span class="chart-center-main">{{ $clicksCount }}</span>
                            <span class="chart-center-label">Personas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-info text-center">
            No hay datos de clics registrados aún.
        </div>
        @endif
    </div>

    @if($clicksCount > 0)
    <script>
        // Configuración común para gráficas
        const commonChartOptions = {
            cutout: '75%',
            plugins: { legend: { display: false } }
        };

        // Gráfica de emails abiertos
        new Chart(document.getElementById('openedChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [{{ $opened }}, 1], // El 1 es solo para mostrar el gráfico
                    backgroundColor: ['#28a745', '#f0f0f0']
                }]
            },
            options: commonChartOptions
        });

        // Gráfica de clics realizados
        new Chart(document.getElementById('clickedChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [{{ $clicksCount }}, 1], // El 1 es solo para mostrar el gráfico
                    backgroundColor: ['#007bff', '#f0f0f0']
                }]
            },
            options: commonChartOptions
        });
    </script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>