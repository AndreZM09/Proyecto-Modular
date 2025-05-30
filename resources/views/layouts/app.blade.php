<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Email Marketing</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/estadisticas.css') }}" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        body {
            padding-top: 70px !important; /* Espacio para la barra de navegación fija */
            margin-top: 0 !important;
        }
        .navbar {
            margin: 0 !important;
            padding: 0.5rem 1rem;
            border-radius: 0;
        }
        .container-fluid {
            padding-top: 0;
        }
    </style>
</head>
<body>
    <!-- Incluir la Barra de Navegación -->
    @include('layouts.navbar')

    <!-- Contenido de la página -->
    <div class="container-fluid p-0">
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Scripts adicionales -->
    @stack('scripts')
</body>
</html>
