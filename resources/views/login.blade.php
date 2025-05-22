<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campañas de Marketing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="{{ asset('css/login.css') }}" rel="stylesheet" />
    <!-- Meta CSRF token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="login-page">

    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="login-box">
            <div class="logo-container">
                <img src="{{ asset('imagenes/logo.png') }}" class="avatar" alt="Avatar Image">
            </div>
            <h1>Campañas de Marketing</h1>
            <div id="message" class="alert"></div>
            <form id="loginForm">
                @csrf
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" placeholder="Ingresa tu correo" required>
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" placeholder="Ingresa tu contraseña" required>
                <button type="submit" class="btn btn-primary w-100 d-block mx-auto">Iniciar Sesión</button>
                <div class="mt-3">
                    <a href="#">¿Olvidaste tu contraseña?</a><br>
                    <a href="#">¿No tienes una cuenta?</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Incluir jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Configuración global de AJAX -->
    <script>
       $('#loginForm').submit(function(e) {
    e.preventDefault();

    $.ajax({
        url: '/login',
        method: 'POST',
        data: {
            email: $('#email').val(),
            password: $('#password').val(),
            _token: $('input[name="_token"]').val()
        },
        success: function(response) {
            if (response.success) {
                window.location.href = response.redirect;
            } else {
                alert(response.message);
            }
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            alert('Error en el servidor');
        }
    });
});

    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
