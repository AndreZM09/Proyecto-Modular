<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Predicciones de Campañas con IA</title>
    <!-- Incluye Tailwind CSS si lo estás usando en tu proyecto, o tu CSS base -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        h1 {
            color: #2c3e50;
            font-size: 2.2em;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 700;
        }
        .loading-spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: #4f46e5; /* Color del spinner */
            animation: spin 1s ease infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        #aiResponse {
            background-color: #f8faff;
            border: 1px solid #e0e7ff;
            padding: 25px;
            border-radius: 8px;
            white-space: pre-wrap; /* Para preservar saltos de línea y espacios de la respuesta de la IA */
            line-height: 1.6;
            font-size: 1.1em;
            color: #2d3748;
        }
        .error-message {
            color: #e53e3e;
            background-color: #fed7d7;
            border: 1px solid #ef4444;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-top: 20px;
        }
        button {
            background-color: #4f46e5;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: block;
            margin: 30px auto 0;
            border: none;
        }
        button:hover {
            background-color: #4338ca;
        }
        button:disabled {
            background-color: #a7a3ff;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="container">
        <h1>Predicciones y Recomendaciones de la IA para tus Campañas</h1>
        
        <p class="text-center text-gray-600 mb-8">
            Haz clic en el botón para que la IA analice tus campañas anteriores y te brinde información valiosa y predicciones para futuras campañas.
        </p>

        <button id="getPredictionsBtn">Obtener Predicciones de la IA</button>

        <div id="loading" class="hidden loading-spinner"></div>
        <div id="aiResponse" class="mt-8 hidden"></div>
        <div id="errorMessage" class="error-message hidden"></div>

    </div>

    <script>
        document.getElementById('getPredictionsBtn').addEventListener('click', async () => {
            const button = document.getElementById('getPredictionsBtn');
            const loading = document.getElementById('loading');
            const aiResponseDiv = document.getElementById('aiResponse');
            const errorMessageDiv = document.getElementById('errorMessage');

            // Resetear estados
            aiResponseDiv.classList.add('hidden');
            errorMessageDiv.classList.add('hidden');
            errorMessageDiv.textContent = '';
            button.disabled = true;
            loading.classList.remove('hidden');

            try {
                const response = await fetch('/api/campaign-predictions');
                const data = await response.json();

                if (data.success) {
                    aiResponseDiv.textContent = data.predictions_and_recommendations;
                    aiResponseDiv.classList.remove('hidden');
                } else {
                    errorMessageDiv.textContent = data.message || 'Ocurrió un error inesperado al obtener las predicciones.';
                    errorMessageDiv.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error al llamar a la API de predicciones:', error);
                errorMessageDiv.textContent = 'No se pudo conectar con el servidor para obtener las predicciones. Verifica tu conexión o la configuración de la API.';
                errorMessageDiv.classList.remove('hidden');
            } finally {
                loading.classList.add('hidden');
                button.disabled = false;
            }
        });
    </script>
</body>
</html> 