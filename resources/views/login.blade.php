<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campañas de Marketing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet" /> <!-- Navbar CSS -->
    <link href="{{ asset('css/login.css') }}" rel="stylesheet" /> <!-- Login CSS -->
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

            <!-- Contenedor para mensajes de éxito/error -->
            <div id="message" class="alert"></div>

            <!-- Formulario de inicio de sesión -->
            <form id="loginForm">
                @csrf
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" placeholder="Ingresa tu correo" required>

                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" placeholder="Ingresa tu contraseña" required>

                <button type="submit" class="btn btn-primary w-100 d-block mx-auto">Iniciar Sesión</button>



                <!-- Enlaces debajo del botón -->
                <div class="mt-3"> <!-- Añadir margen superior -->
                    <a href="#">¿Olvidaste tu contraseña?</a><br>
                    <a href="#">¿No tienes una cuenta?</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Incluir jQuery (necesario para AJAX) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Script para manejar el envío del formulario con AJAX -->
    <script>
        $(document).ready(function () {
            // Manejar el envío del formulario
            $('#loginForm').on('submit', function (e) {
                e.preventDefault(); // Evitar que el formulario se envíe de manera tradicional
    
                // Obtener los datos del formulario
                var formData = $(this).serialize();
    
                // Enviar los datos con AJAX
                $.ajax({
                    url: '{{ route("login.submit") }}', // Ruta a la que se enviarán los datos
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.success) {
                            // Mostrar mensaje de éxito
                            $('#message').removeClass('alert-danger').addClass('alert-success').text(response.message).fadeIn();
                            setTimeout(function () {
                                // Redirigir a la URL proporcionada por el servidor
                                window.location.href = response.redirect;
                            }, 2000); // Redirigir después de 2 segundos
                        } else {
                            // Mostrar mensaje de error
                            $('#message').removeClass('alert-success').addClass('alert-danger').text(response.message).fadeIn();
                        }
                    },
                    error: function (xhr) {
                        // Mostrar mensaje de error si hay un problema con la solicitud
                        $('#message').removeClass('alert-success').addClass('alert-danger').text('Error en la solicitud. Inténtalo de nuevo.').fadeIn();
                    }
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>