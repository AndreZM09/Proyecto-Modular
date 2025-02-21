<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campañas de Marketing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet" /> <!-- Navbar CSS -->
    <link href="{{ asset('css/login.css') }}" rel="stylesheet" /> <!-- Login CSS -->
    <style>
        /* Fondo dinámico desde PHP */
        body.login-page {
            margin: 0;
            padding: 0;
            background: url('{{ asset("imagenes/fondo.png") }}') no-repeat center center fixed;
            background-size: cover;
            background-position: center 70px; /* Mueve la imagen 70px hacia abajo */
            font-family: sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>

<body class="login-page"> <!-- Clase agregada -->

    <!-- ✅ Barra de Navegación fuera del body -->
    @include('layouts.navbar')

    <!-- ✅ Contenedor del Login -->
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="login-box">
            <!-- Logo con fondo blanco circular -->
            <div class="logo-container">
                <img src="{{ asset('imagenes/logo.png') }}" class="avatar" alt="Avatar Image">
            </div>
            <h1>Campañas de Marketing</h1>
            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" placeholder="Ingresa tu correo" required>

                <label for="password">Contraseña</label>
                <input type="password" name="password" placeholder="Ingresa tu contraseña" required>

                <input type="submit" value="Iniciar Sesión">

                <a href="#">¿Olvidaste tu contraseña?</a><br>
                <a href="#">¿No tienes una cuenta?</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>