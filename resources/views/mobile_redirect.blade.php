<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirigiendo...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .loading-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .message {
            color: #333;
            margin-bottom: 15px;
        }
        .url {
            color: #007bff;
            font-size: 14px;
            word-break: break-all;
            margin-bottom: 20px;
        }
        .fallback-link {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        .fallback-link:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="loading-container">
        <div class="spinner"></div>
        <div class="message">Redirigiendo a tu destino...</div>
        <div class="url">{{ $redirectUrl }}</div>
        
        <a href="{{ $redirectUrl }}" class="fallback-link" id="fallbackLink">
            Si no eres redirigido automáticamente, haz clic aquí
        </a>
    </div>

    <script>
        // Función para redirigir
        function redirectToUrl() {
            try {
                // Intentar redirección con window.location
                window.location.href = '{{ $redirectUrl }}';
            } catch (error) {
                console.error('Error en redirección:', error);
                // Si falla, mostrar el enlace de respaldo
                document.getElementById('fallbackLink').style.display = 'inline-block';
            }
        }

        // Intentar múltiples métodos de redirección
        function attemptRedirect() {
            // Método 1: Redirección inmediata
            redirectToUrl();
            
            // Método 2: Redirección con timeout (por si el primer método falla)
            setTimeout(function() {
                if (window.location.href !== '{{ $redirectUrl }}') {
                    console.log('Primer método falló, intentando segundo método...');
                    window.location.replace('{{ $redirectUrl }}');
                }
            }, 1000);
            
            // Método 3: Redirección con window.open (para algunos navegadores móviles)
            setTimeout(function() {
                if (window.location.href !== '{{ $redirectUrl }}') {
                    console.log('Segundo método falló, intentando tercer método...');
                    try {
                        window.open('{{ $redirectUrl }}', '_self');
                    } catch (error) {
                        console.error('Error en tercer método:', error);
                    }
                }
            }, 2000);
        }

        // Ejecutar redirección cuando la página se carga
        document.addEventListener('DOMContentLoaded', function() {
            // Pequeño delay para asegurar que la página esté completamente cargada
            setTimeout(attemptRedirect, 100);
        });

        // También intentar redirección inmediata
        attemptRedirect();

        // Log para debugging
        console.log('Página de redirección móvil cargada');
        console.log('URL de destino:', '{{ $redirectUrl }}');
        console.log('User Agent:', navigator.userAgent);
    </script>
</body>
</html>
