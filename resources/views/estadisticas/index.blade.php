@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="text-center">📊 Estadísticas</h1>
    
    <p class="text-center">Total de correos rastreados (envíos): <span class="badge bg-primary">{{ $totalEmailsSent }}</span></p>

    <div class="row">
        <div class="col-md-6">
            <div class="chart-container">
                <h3>Emails Abiertos vs No Abiertos</h3>
                <div class="chart-wrapper double-value">
                    <canvas id="openedChart"></canvas>
                    <div class="chart-center-text">
                        <span class="chart-center-main">{{ $totalEmailsOpened }}</span>
                        <span class="chart-center-label">Abiertos</span>
                        <span class="chart-center-secondary">{{ $totalEmailsSent - $totalEmailsOpened }}</span>
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
                        <span class="chart-center-main">{{ $totalClicks }}</span>
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
                        <span class="chart-center-main">{{ $totalEmailsSent - $totalClicks }}</span>
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
                        <span class="chart-center-main">{{ $totalEmailsSent }}</span>
                        <span class="chart-center-label">Personas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
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
                data: [{{ $totalEmailsOpened }}, {{ $totalEmailsSent - $totalEmailsOpened }}],
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
                data: [{{ $totalClicks }}, {{ $totalEmailsSent - $totalClicks }}],
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
                data: [{{ $totalEmailsSent - $totalClicks }}, {{ $totalClicks }}],
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
                data: [{{ $totalEmailsSent }}, 0],
                backgroundColor: ['#ffc107', '#f0f0f0'],
                borderWidth: 0
            }]
        },
        options: gaugeOptions
    });
</script>
@endpush
@endsection 