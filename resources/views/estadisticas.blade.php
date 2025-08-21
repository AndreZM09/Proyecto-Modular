<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Estadísticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/estadisticas.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            padding-top: 70px !important; /* Espacio para la barra de navegación fija */
        }
        .campaign-card {
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .campaign-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .campaign-image {
            height: 120px;
            object-fit: contain;
            margin: 0 auto;
            display: block;
            border-radius: 8px;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
    <style>
        /* Estilos para el botón flotante y el chat */
        .chatbot-fab {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background-color: #007bff; /* Color de botón flotante */
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.8rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        .chatbot-fab:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
        .chatbot-container {
            position: fixed;
            bottom: 90px; /* Arriba del FAB */
            right: 20px;
            width: 350px; /* Ancho del chat */
            height: 450px; /* Alto del chat */
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 999;
            transform: translateY(20px);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease-in-out;
        }
        .chatbot-container.active {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }
        .chatbot-header {
            background-color: #007bff;
            color: white;
            padding: 15px;
            font-size: 1.1em;
            font-weight: bold;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chatbot-header .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5em;
            cursor: pointer;
        }
        .chatbot-body {
            flex-grow: 1;
            padding: 15px;
            overflow-y: auto;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        .chatbot-message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 8px;
            max-width: 80%;
            line-height: 1.4;
        }
        .chatbot-message.user {
            background-color: #e2f2ff; /* Light blue */
            margin-left: auto;
            text-align: right;
        }
        .chatbot-message.ai {
            background-color: #e9ecef; /* Light gray */
            margin-right: auto;
            text-align: left;
        }
        .chatbot-message.ai.error {
            background-color: #f8d7da; /* Light red */
            color: #721c24;
        }
        .chatbot-input-area {
            padding: 15px;
            display: flex;
            border-top: 1px solid #dee2e6;
            background-color: #ffffff;
        }
        .chatbot-input-area textarea {
            flex-grow: 1;
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 8px;
            margin-right: 10px;
            resize: none; /* Deshabilitar redimensionamiento manual */
            overflow: hidden; /* Ocultar barras de desplazamiento */
        }
        .chatbot-input-area button {
            background-color: #28a745; /* Green send button */
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 15px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .chatbot-input-area button:hover {
            background-color: #218838;
        }
        .chatbot-input-area button:disabled {
            background-color: #90ee90;
            cursor: not-allowed;
        }
        .chatbot-spinner {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }
        /* Media queries para responsividad */
        @media (max-width: 768px) {
            .chatbot-container {
                width: 90%;
                right: 5%;
                bottom: 90px;
                height: 70vh; /* Ocupa más alto en móviles */
            }
            .chatbot-fab {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
                bottom: 15px;
                right: 15px;
            }
        }
    </style>
</head>
    
<body class="estadisticas-page">
    @include('layouts.navbar')

    <!-- Contenido existente de la página -->
    <div class="container mt-4">
        @if(isset($campaignTitle))
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-bar-chart-line"></i>
                    Estadísticas: {{ $campaignTitle }}
                </h1>
                <a href="{{ route('campañas') }}" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Volver a Campañas
                </a>
            </div>
        @else
            <h1 class="text-center">📊 Estadísticas</h1>
        @endif
        
        <p class="text-center">Total de correos enviados: <span class="badge bg-primary">{{ $emailsSent }}</span></p>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="chart-container">
                    <h3>Correos Abiertos</h3>
                    <div class="chart-wrapper">
                        <div class="chart-center-text">
                            <span class="chart-center-main">{{ $opened }}</span>
                            <span class="chart-center-label">Abiertos</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="chart-container">
                    <h3>Correos Enviados</h3>
                    <div class="chart-wrapper">
                        <canvas id="sentChart"></canvas>
                        <div class="chart-center-text">
                            <span class="chart-center-main">{{ $emailsSent }}</span>
                            <span class="chart-center-label">Enviados</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="chart-container">
                    <h3>Clics en Imagen</h3>
                    <div class="chart-wrapper">
                        <canvas id="clickedChart"></canvas>
                        <div class="chart-center-text">
                            <span class="chart-center-main">{{ $clicksCount }}</span>
                            <span class="chart-center-label">Clics</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas por Campaña -->
        @if(!isset($campaign))
        <h2 class="mt-5 mb-4">
            <i class="bi bi-bar-chart-line"></i>
            Estadísticas por Campaña
        </h2>
        @endif
        
        @if(count($campaignStats) > 0)
            <div class="row">
                @foreach($campaignStats as $campaign)
                <div class="col-md-{{ isset($campaignTitle) ? '12' : '4' }}">
                    <div class="card campaign-card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $campaign['name'] }}</h5>
                            <p class="text-muted small">{{ $campaign['date'] }}</p>
                            
                            <div class="row align-items-center">
                                @if(isset($campaign['image']))
                                <div class="{{ isset($campaignTitle) ? 'col-md-3' : 'col-12' }}">
                                    <img src="{{ asset('storage/email_images/' . $campaign['image']) }}" 
                                         class="campaign-image my-3" alt="Imagen de campaña">
                                </div>
                                @endif
                                
                                <div class="{{ isset($campaignTitle) ? 'col-md-9' : 'col-12' }}">
                                    <div class="row text-center mt-3">
                                        <div class="col-4">
                                            <div class="stat-value text-primary">{{ $campaign['sent'] }}</div>
                                            <div class="stat-label">Enviados</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="stat-value text-success">{{ $campaign['clicks'] }}</div>
                                            <div class="stat-label">Clics</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="stat-value text-info">{{ $campaign['open_rate'] }}%</div>
                                            <div class="stat-label">Tasa</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                No hay datos de campañas disponibles.
            </div>
        @endif
    </div>

    <!-- Sección de detalles de la campaña -->
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Detalles de la Campaña</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Imagen de la campaña -->
                    <div class="col-md-4">
                        @if(isset($currentImage) || isset($campaign))
                            @php
                                $imageDetails = isset($campaign) ? $campaign : $currentImage;
                                $filename = is_array($imageDetails) ? $imageDetails['image'] : $imageDetails->filename;
                            @endphp
                            <img src="{{ asset('storage/email_images/' . $filename) }}" 
                                 alt="Imagen de la campaña" 
                                 class="img-fluid rounded">
                        @else
                            <div class="alert alert-info">
                                No hay imagen configurada para esta campaña
                            </div>
                        @endif
                    </div>
                    <!-- Detalles de la campaña -->
                    <div class="col-md-8">
                        @if(isset($currentImage) || isset($campaign))
                            @php
                                $imageDetails = isset($campaign) ? $campaign : $currentImage;
                                $subject = is_array($imageDetails) ? ($imageDetails['name'] ?? 'Sin asunto') : ($imageDetails->subject ?: 'Sin asunto');
                                $created_at = is_array($imageDetails) ? ($imageDetails['date'] ?? 'Fecha no disponible') : $imageDetails->created_at->format('d/m/Y H:i');
                                $description = is_array($imageDetails) ? ($imageDetails['description'] ?? 'Sin descripción') : ($imageDetails->description ?: 'Sin descripción');
                                $priority = is_array($imageDetails) ? ($imageDetails['priority'] ?? 'normal') : ($imageDetails->priority ?: 'normal');
                            @endphp
                            <h4>{{ $subject }}</h4>
                            <p class="text-muted mb-2">
                                <strong>Fecha de creación:</strong> 
                                {{ $created_at }}
                            </p>
                            <p class="mb-2">
                                <strong>Descripción:</strong><br>
                                {{ $description }}
                            </p>
                            <p class="mb-2">
                                <strong>Prioridad:</strong>
                                <span class="badge {{ $priority === 'high' ? 'bg-danger' : ($priority === 'urgent' ? 'bg-warning' : 'bg-success') }}">
                                    {{ ucfirst($priority) }}
                                </span>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de correos enviados -->
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lista de Correos Enviados</h5>
                <span class="badge bg-light text-dark">Total: {{ count($emailList) }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Correo</th>
                                <th>Fecha de Envío</th>
                                <th>Estado</th>
                                <th>Última Interacción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($emailList as $email)
                                <tr>
                                    <td>{{ $email->email }}</td>
                                    <td>{{ $email->email_sent_at ? $email->email_sent_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td>
                                        @if($email->ip_address)
                                            <span class="badge bg-success">Clic realizado</span>
                                        @else
                                            <span class="badge bg-secondary">Sin interacción</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($email->clicked_at)
                                            Clic realizado ({{ $email->clicked_at->format('d/m/Y H:i') }})
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No hay correos enviados para esta campaña</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Botón flotante para abrir el chat de la IA -->
    <div class="chatbot-fab" id="openChatBtn">
        <i class="bi bi-chat-dots-fill"></i>
    </div>

    <!-- Contenedor del Chatbot (inicialmente oculto) -->
    <div class="chatbot-container" id="chatbotContainer">
        <div class="chatbot-header">
            <span>Agente IA</span>
            <button class="close-btn" id="closeChatBtn">&times;</button>
        </div>
        <div class="chatbot-body" id="chatbox">
            <!-- Mensajes del chat aquí -->
            <div class="chatbot-message ai">
                ¡Hola! Soy tu asistente IA para campañas. Pregúntame sobre tus estadísticas.
            </div>
        </div>
        <div class="chatbot-input-area">
            <textarea id="aiQuestionInput" placeholder="Escribe tu pregunta..." rows="1"></textarea>
            <button id="askAiBtn">
                <span id="spinner" class="spinner-border spinner-border-sm hidden" role="status" aria-hidden="true"></span>
                Enviar
            </button>
        </div>
    </div>

    <script>
        // Lógica de los gráficos (ya existente)
        const commonChartOptions = {
            cutout: '75%',
            plugins: { legend: { display: true, position: 'bottom' } }
        };

        new Chart(document.getElementById('sentChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Enviados', 'No Enviados'],
                datasets: [{
                    data: [{{ $emailsSent }}, {{ max(0, 100 - $emailsSent) }}],
                    backgroundColor: ['#28a745', '#f0f0f0']
                }]
            },
            options: commonChartOptions
        });

        new Chart(document.getElementById('clickedChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Con Clic', 'Sin Clic'],
                datasets: [{
                    data: [{{ $clicksCount }}, {{ ($emailsSent > $clicksCount) ? ($emailsSent - $clicksCount) : 0 }}],
                    backgroundColor: ['#007bff', '#f0f0f0']
                }]
            },
            options: commonChartOptions
        });

        // Lógica del Agente IA Chatbot
        const openChatBtn = document.getElementById('openChatBtn');
        const closeChatBtn = document.getElementById('closeChatBtn');
        const chatbotContainer = document.getElementById('chatbotContainer');
        const aiQuestionInput = document.getElementById('aiQuestionInput');
        const askAiBtn = document.getElementById('askAiBtn');
        const spinner = document.getElementById('spinner');
        const chatbox = document.getElementById('chatbox');

        openChatBtn.addEventListener('click', () => {
            chatbotContainer.classList.add('active');
        });

        closeChatBtn.addEventListener('click', () => {
            chatbotContainer.classList.remove('active');
        });

        // Auto-redimensionar el textarea
        aiQuestionInput.addEventListener('input', () => {
            aiQuestionInput.style.height = 'auto';
            aiQuestionInput.style.height = (aiQuestionInput.scrollHeight) + 'px';
        });

        askAiBtn.addEventListener('click', async () => {
            const question = aiQuestionInput.value.trim();
            if (!question) return;

            // Añadir pregunta del usuario al chat
            addMessage(question, 'user');
            aiQuestionInput.value = '';
            aiQuestionInput.style.height = 'auto'; // Reset height

            askAiBtn.disabled = true;
            spinner.classList.remove('hidden');

            try {
                const response = await fetch('/api/ask-ai', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ question: question })
                });
                const data = await response.json();

                if (data.success) {
                    addMessage(data.response, 'ai');
                } else {
                    addMessage('Error: ' + (data.message || 'No se pudo obtener una respuesta válida.'), 'ai error');
                    console.error('Error AI Response:', data.error); // Log detailed error
                }
            } catch (error) {
                console.error('Error al comunicarse con la IA:', error);
                addMessage('Lo siento, no pude conectar con la IA. Por favor, verifica tu conexión o la configuración.', 'ai error');
            } finally {
                askAiBtn.disabled = false;
                spinner.classList.add('hidden');
                chatbox.scrollTop = chatbox.scrollHeight; // Scroll al final del chat
            }
        });

        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('chatbot-message', sender);
            messageDiv.textContent = text;
            chatbox.appendChild(messageDiv);
            chatbox.scrollTop = chatbox.scrollHeight; // Auto-scroll
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>