<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet" /> <!-- Navbar CSS -->
    <link href="{{ asset('css/estadisticas.css') }}" rel="stylesheet" /> <!-- Estadísticas CSS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>


<body class="estadisticas-page"> <!-- Clase agregada -->

    <!-- ✅ Barra de Navegación fuera del body -->
    @include('layouts.navbar')

    <!-- ✅ Contenido de la página de estadísticas -->
    <div class="container mt-4">
        <h1>Estadísticas</h1>
        <p>Total de registros en la tabla <strong>clicks</strong>: <span class="badge bg-primary">{{ $clicksCount }}</span></p>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Fecha de Creación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clicks as $click)
                <tr>
                    <td>{{ $click->id }}</td>
                    <td>{{ $click->email }}</td>
                    <td>{{ $click->created_at }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>