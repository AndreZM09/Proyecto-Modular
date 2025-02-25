<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/estadisticas.css') }}" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="estadisticas-page">

    @include('layouts.navbar')

    <div class="container mt-4">
        <h1>Estadísticas</h1>
        <p>Total de registros en la tabla <strong>clicks</strong>: <span class="badge bg-primary">{{ $clicksCount }}</span></p>

        <!-- 📊 Gráfica Circular: Emails Abiertos vs No Abiertos -->
        <div class="chart-container">
            <h3>Emails Abiertos vs No Abiertos</h3>
            <canvas id="openedChart"></canvas>
        </div>

        <!-- 📊 Gráfica de Barras: Clics por Zona (IP) -->
        <div class="chart-container">
            <h3>Clics por Zona (IP)</h3>
            <canvas id="zoneChart"></canvas>
        </div>

        <!-- 📊 Gráfica de Barras: Personas que hicieron clic -->
        <div class="chart-container">
            <h3>Personas que hicieron clic</h3>
            <canvas id="clickedChart"></canvas>
        </div>

        <!-- 📊 Gráfica de Barras: Personas que no hicieron clic -->
        <div class="chart-container">
            <h3>Personas que no hicieron clic</h3>
            <canvas id="notClickedChart"></canvas>
        </div>

        <!-- 📊 Gráfica de Barras: Personas que recibieron el mail -->
        <div class="chart-container">
            <h3>Personas que recibieron el mail</h3>
            <canvas id="receivedChart"></canvas>
        </div>
    </div>

    <script>
        // 📊 Gráfica Circular
        const openedCtx = document.getElementById('openedChart').getContext('2d');
        new Chart(openedCtx, {
            type: 'pie',
            data: {
                labels: ['Abiertos', 'No Abiertos'],
                datasets: [{
                    data: [{{ $opened }}, {{ $notOpened }}],
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            }
        });

        // 📊 Gráfica de Barras por IP
        const zoneCtx = document.getElementById('zoneChart').getContext('2d');
        new Chart(zoneCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($clicksByZone->pluck('ip_address')) !!},
                datasets: [{
                    label: 'Clics por IP',
                    data: {!! json_encode($clicksByZone->pluck('total')) !!},
                    backgroundColor: '#007bff'
                }]
            }
        });

        // 📊 Gráfica de Barras: Personas que hicieron clic
        const clickedCtx = document.getElementById('clickedChart').getContext('2d');
        new Chart(clickedCtx, {
            type: 'bar',
            data: {
                labels: ['Personas que hicieron clic'],
                datasets: [{
                    label: 'Clics',
                    data: [{{ $clicksCount }}],
                    backgroundColor: '#28a745'
                }]
            }
        });

        // 📊 Gráfica de Barras: Personas que no hicieron clic
        const notClickedCtx = document.getElementById('notClickedChart').getContext('2d');
        new Chart(notClickedCtx, {
            type: 'bar',
            data: {
                labels: ['Personas que no hicieron clic'],
                datasets: [{
                    label: 'No Clics',
                    data: [{{ $totalEmails - $clicksCount }}],
                    backgroundColor: '#dc3545'
                }]
            }
        });

        // 📊 Gráfica de Barras: Personas que recibieron el mail
        const receivedCtx = document.getElementById('receivedChart').getContext('2d');
        new Chart(receivedCtx, {
            type: 'bar',
            data: {
                labels: ['Personas que recibieron el mail'],
                datasets: [{
                    label: 'Recibidos',
                    data: [{{ $totalEmails }}],
                    backgroundColor: '#ffc107'
                }]
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>