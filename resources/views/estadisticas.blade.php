<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet" /> <!-- Navbar CSS -->
    <link href="{{ asset('css/estadisticas.css') }}" rel="stylesheet" /> <!-- Estadísticas CSS -->
</head>

<body class="estadisticas-page"> <!-- Clase agregada -->

    <!-- ✅ Barra de Navegación fuera del body -->
    @include('layouts.navbar')

    <!-- ✅ Contenido de la página de estadísticas -->
    <div class="container">
        <h1>Estadísticas</h1>
        <p>Bienvenido a la página de estadísticas.</p>
        <!-- Aquí puedes agregar más contenido, gráficos, tablas, etc. -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>