<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Redirecciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .test-section {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .test-link {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .test-link:hover {
            background: #0056b3;
        }
        .info-box {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .test-image {
            max-width: 300px;
            border: 2px solid #007bff;
            border-radius: 5px;
            margin: 10px 0;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test de Redirecciones</h1>
        
        <div class="info-box">
            <h3>Información de Prueba</h3>
            <p><strong>Email de prueba:</strong> {{ $testEmail }}</p>
            <p><strong>Email codificado:</strong> {{ $testEmailEncoded }}</p>
            <p><strong>ID de imagen:</strong> {{ $imageId }}</p>
        </div>

        <div class="test-section">
            <h3>Test de Redirección de Click</h3>
            <p>Haz clic en la imagen para probar la redirección:</p>
            
            <a href="{{ route('clicks.track', ['id_img' => $imageId, 'email' => $testEmail]) }}" target="_blank">
                <img src="{{ asset('storage/email_images/default.jpg') }}" 
                     alt="Imagen de prueba" 
                     class="test-image"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2Y4ZjlmYSIvPjx0ZXh0IHg9IjE1MCIgeT0iMTAwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTYiIGZpbGw9IiM2Yzc1N2QiIHRleHQtYW5jaG9yPSJtaWRkbGUiPkltYWdlbiBkZSBQcnVlYmE8L3RleHQ+PC9zdmc+'">
            </a>
            
            <p><strong>URL de redirección:</strong></p>
            <pre>{{ route('clicks.track', ['id_img' => $imageId, 'email' => $testEmail]) }}</pre>
        </div>

        <div class="test-section">
            <h3>Test de Apertura de Email</h3>
            <p>Este enlace simula la apertura de un email:</p>
            <a href="{{ route('clicks.open', ['id_img' => $imageId, 'email' => $testEmail]) }}" 
               class="test-link" target="_blank">
                Simular Apertura de Email
            </a>
            
            <p><strong>URL de apertura:</strong></p>
            <pre>{{ route('clicks.open', ['id_img' => $imageId, 'email' => $testEmail]) }}</pre>
        </div>

        <div class="test-section">
            <h3>Tests de Detección de Dispositivos</h3>
            <a href="{{ url('/test-device-detection') }}" class="test-link">
                Test de Detección de Dispositivos
            </a>
            <a href="{{ url('/test-mobile-redirect') }}" class="test-link">
                API Test Móvil
            </a>
            <a href="{{ url('/test-redirect/1/test@example.com') }}" class="test-link">
                Test de Redirección con Parámetros
            </a>
        </div>

        <div class="test-section">
            <h3>Información del Navegador</h3>
            <p><strong>User Agent:</strong> <span id="userAgent"></span></p>
            <p><strong>Es móvil:</strong> <span id="isMobile"></span></p>
            <p><strong>URL actual:</strong> <span id="currentUrl"></span></p>
        </div>
    </div>

    <script>
        // Detección de móvil con JavaScript
        function isMobileDevice() {
            const userAgent = navigator.userAgent || navigator.vendor || window.opera;
            const mobileKeywords = [
                'Mobile', 'Android', 'iPhone', 'iPad', 'Windows Phone', 
                'BlackBerry', 'Opera Mini', 'IEMobile', 'Mobile Safari',
                'CriOS', 'FxiOS', 'OPiOS', 'Vivaldi'
            ];
            
            for (let keyword of mobileKeywords) {
                if (userAgent.toLowerCase().indexOf(keyword.toLowerCase()) > -1) {
                    return true;
                }
            }
            return false;
        }

        // Actualizar información del navegador
        document.getElementById('userAgent').textContent = navigator.userAgent;
        document.getElementById('isMobile').textContent = isMobileDevice() ? 'Sí' : 'No';
        document.getElementById('currentUrl').textContent = window.location.href;

        console.log('Test de redirecciones cargado');
        console.log('User Agent:', navigator.userAgent);
        console.log('Es móvil:', isMobileDevice());
    </script>
</body>
</html>
