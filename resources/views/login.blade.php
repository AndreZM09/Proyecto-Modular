<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campañas de Marketing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/login.css') }}" rel="stylesheet" />
    <!-- Meta CSRF token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="login-page">
    @include('layouts.navbar')

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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function () {
            $('#loginForm').on('submit', function (e) {
                e.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    url: '{{ route("login.submit") }}',
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.success) {
                            $('#message').removeClass('alert-danger').addClass('alert-success')
                                .text(response.message).fadeIn();
                            setTimeout(function () {
                                window.location.href = response.redirect;
                            }, 2000);
                        } else {
                            $('#message').removeClass('alert-success').addClass('alert-danger')
                                .text(response.message).fadeIn();
                        }
                    },
                    error: function (xhr) {
                        $('#message').removeClass('alert-success').addClass('alert-danger')
                            .text('Error en la solicitud. Inténtalo de nuevo.').fadeIn();
                    }
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
