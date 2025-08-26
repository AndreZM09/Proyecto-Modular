<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Detección de Dispositivos</title>
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
        .status {
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .mobile {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .desktop {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .keyword {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            margin: 2px;
            font-size: 12px;
        }
        .test-links {
            margin-top: 20px;
        }
        .test-links a {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .test-links a:hover {
            background: #218838;
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
        <h1>Test de Detección de Dispositivos</h1>
        
        <div class="status {{ $isMobile ? 'mobile' : 'desktop' }}">
            <h3>Estado del Dispositivo</h3>
            <p><strong>Dispositivo detectado:</strong> {{ $isMobile ? 'Móvil' : 'Escritorio' }}</p>
        </div>

        <div class="status info">
            <h3>Información del Navegador</h3>
            <p><strong>User Agent:</strong></p>
            <pre>{{ $userAgent }}</pre>
            
            <p><strong>URL actual:</strong> {{ $currentUrl }}</p>
            <p><strong>URL completa:</strong> {{ $fullUrl }}</p>
        </div>

        @if(count($mobileKeywords) > 0)
        <div class="status mobile">
            <h3>Palabras Clave Móviles Detectadas</h3>
            @foreach($mobileKeywords as $keyword)
                <span class="keyword">{{ $keyword }}</span>
            @endforeach
        </div>
        @else
        <div class="status desktop">
            <h3>No se detectaron palabras clave móviles</h3>
            <p>Este dispositivo parece ser de escritorio.</p>
        </div>
        @endif

        <div class="test-links">
            <h3>Enlaces de Prueba</h3>
            <a href="{{ route('test.click') }}">Test de Click Básico</a>
            <a href="{{ url('/test-redirect/1/test@example.com') }}">Test de Redirección</a>
            <a href="{{ url('/test-mobile-redirect') }}">API Test Móvil</a>
        </div>

        <div class="status info">
            <h3>Información de JavaScript</h3>
            <p><strong>User Agent (JS):</strong> <span id="jsUserAgent"></span></p>
            <p><strong>Es móvil (JS):</strong> <span id="jsIsMobile"></span></p>
            <p><strong>URL actual (JS):</strong> <span id="jsCurrentUrl"></span></p>
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

        // Actualizar información de JavaScript
        document.getElementById('jsUserAgent').textContent = navigator.userAgent;
        document.getElementById('jsIsMobile').textContent = isMobileDevice() ? 'Sí' : 'No';
        document.getElementById('jsCurrentUrl').textContent = window.location.href;

        console.log('Test de detección de dispositivos cargado');
        console.log('User Agent:', navigator.userAgent);
        console.log('Es móvil (JS):', isMobileDevice());
    </script>
</body>
</html>
