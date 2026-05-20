{{--
    Asistente IA - Partial reutilizable
    ====================================
    Incluir con: @include('partials.chatbot')

    Este partial es autónomo: CSS + HTML + JS van juntos. El JS:
      - Espera a DOMContentLoaded antes de inicializar
      - NO depende de Bootstrap JS (usa CSS para mostrar/ocultar)
      - Se aísla en un IIFE para no contaminar el scope global

    Por defecto se incluye en layouts/app.blade.php (cubre vistas con
    @extends). Para vistas standalone (estadisticas, correos, etc.),
    incluirlo manualmente antes de </body>.

    Si quieres ocultar el chatbot en alguna vista específica (ej: login),
    define la variable @php $hideChatbot = true; @endphp antes del @include.
--}}

@if(empty($hideChatbot))

<style>
/* Chatbot: botón flotante + ventana */
.chatbot-fab {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    background-color: #007bff;
    color: white;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.8rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    cursor: pointer;
    z-index: 1050;
    transition: all 0.3s ease;
    border: none;
}
.chatbot-fab:hover {
    background-color: #0056b3;
    transform: scale(1.05);
}
.chatbot-container {
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 350px;
    height: 450px;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 1049;
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
    line-height: 1;
    padding: 0;
}
.chatbot-body {
    flex-grow: 1;
    padding: 15px;
    overflow-y: auto;
    background-color: #f8f9fa;
}
.chatbot-message {
    margin-bottom: 10px;
    padding: 10px;
    border-radius: 8px;
    max-width: 85%;
    line-height: 1.4;
    word-wrap: break-word;
    white-space: pre-wrap;
    font-size: 0.92rem;
}
.chatbot-message.user {
    background-color: #e2f2ff;
    margin-left: auto;
    text-align: right;
}
.chatbot-message.ai {
    background-color: #e9ecef;
    margin-right: auto;
    text-align: left;
}
.chatbot-message.ai.error {
    background-color: #f8d7da;
    color: #721c24;
}
.chatbot-input-area {
    padding: 12px;
    display: flex;
    gap: 8px;
    border-top: 1px solid #dee2e6;
    background-color: #ffffff;
}
.chatbot-input-area textarea {
    flex-grow: 1;
    border: 1px solid #ced4da;
    border-radius: 8px;
    padding: 8px;
    resize: none;
    overflow: hidden;
    font-family: inherit;
    font-size: 0.9rem;
}
.chatbot-input-area button {
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 14px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    font-size: 0.9rem;
    white-space: nowrap;
}
.chatbot-input-area button:hover {
    background-color: #218838;
}
.chatbot-input-area button:disabled {
    background-color: #90ee90;
    cursor: not-allowed;
}
.chatbot-input-area button.cancel-mode {
    background-color: #dc3545;
}
.chatbot-input-area button.cancel-mode:hover {
    background-color: #c82333;
}

/* Indicador "escribiendo..." */
.typing-indicator {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 0;
}
.typing-indicator span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #888;
    animation: chatbot-typing-bounce 1.2s infinite ease-in-out;
}
.typing-indicator span:nth-child(2) { animation-delay: 0.15s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.3s; }
@keyframes chatbot-typing-bounce {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
    30%           { transform: translateY(-6px); opacity: 1; }
}

/* Cursor parpadeante durante streaming */
.chatbot-message.ai.streaming::after {
    content: '▍';
    display: inline-block;
    margin-left: 2px;
    animation: chatbot-blink 1s steps(2) infinite;
    color: #555;
}
@keyframes chatbot-blink {
    50% { opacity: 0; }
}

@media (max-width: 768px) {
    .chatbot-container {
        width: 90%;
        right: 5%;
        bottom: 90px;
        height: 70vh;
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

<button type="button" class="chatbot-fab" id="chatbotOpenBtn" title="Asistente IA">
    <i class="bi bi-chat-dots-fill"></i>
</button>

<div class="chatbot-container" id="chatbotContainer">
    <div class="chatbot-header">
        <span><i class="bi bi-robot"></i> Asistente IA</span>
        <button class="close-btn" id="chatbotCloseBtn" type="button" aria-label="Cerrar">&times;</button>
    </div>
    <div class="chatbot-body" id="chatbotBox">
        <div class="chatbot-message ai">
            ¡Hola! Soy tu asistente para las campañas. Puedes preguntarme:
            <br>• "¿Qué hora es mejor para enviar correos?"
            <br>• "¿Cuántos abrieron la campaña 1?"
            <br>• "Resumen de mis campañas"
            <br>• "¿Cómo mejorar mi tasa de apertura?"
        </div>
    </div>
    <div class="chatbot-input-area">
        <textarea id="chatbotInput" placeholder="Escribe tu pregunta..." rows="1"></textarea>
        <button id="chatbotSendBtn" type="button">
            <span id="chatbotSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
            <span id="chatbotSendText">Enviar</span>
        </button>
    </div>
</div>

<script>
(function() {
    // Guardia: si el partial se incluye dos veces (ej: en layout + en vista
    // que ya lo tiene), solo inicializamos una vez.
    if (window.__chatbotInitialized) return;
    window.__chatbotInitialized = true;

    function init() {
        const openBtn   = document.getElementById('chatbotOpenBtn');
        const closeBtn  = document.getElementById('chatbotCloseBtn');
        const container = document.getElementById('chatbotContainer');
        const input     = document.getElementById('chatbotInput');
        const sendBtn   = document.getElementById('chatbotSendBtn');
        const sendText  = document.getElementById('chatbotSendText');
        const spinner   = document.getElementById('chatbotSpinner');
        const box       = document.getElementById('chatbotBox');

        if (!openBtn || !container) {
            console.warn('[Chatbot] Elementos no encontrados.');
            return;
        }

        let currentAbort = null;

        openBtn.addEventListener('click', () => {
            container.classList.add('active');
            input.focus();
        });

        closeBtn.addEventListener('click', () => {
            container.classList.remove('active');
            if (currentAbort) currentAbort.abort();
        });

        // Auto-redimensionar textarea
        input.addEventListener('input', () => {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 120) + 'px';
        });

        // Enter = enviar, Shift+Enter = nueva línea
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendBtn.click();
            }
        });

        sendBtn.addEventListener('click', async () => {
            // En modo "cancelar" → abortar
            if (sendBtn.classList.contains('cancel-mode')) {
                if (currentAbort) currentAbort.abort();
                return;
            }

            const question = input.value.trim();
            if (!question) return;

            addMsg(question, 'user');
            input.value = '';
            input.style.height = 'auto';

            const typingMsg = addTyping();
            asCancel();
            currentAbort = new AbortController();

            try {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (!csrfMeta) {
                    throw new Error('Falta <meta name="csrf-token"> en el head.');
                }
                const csrf = csrfMeta.getAttribute('content');

                const response = await fetch('/api/ask-ai', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'text/event-stream',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({ question: question, stream: true }),
                    signal: currentAbort.signal,
                });

                const contentType = response.headers.get('content-type') || '';

                // Si no es SSE, leer como JSON
                if (!contentType.includes('text/event-stream')) {
                    typingMsg.remove();
                    const data = await response.json();
                    if (data.success) {
                        addMsg(data.response, 'ai');
                    } else {
                        addMsg('Error: ' + (data.message || 'Respuesta inválida.'), 'ai error');
                    }
                    return;
                }

                // === Streaming SSE ===
                typingMsg.remove();
                const aiMsg = document.createElement('div');
                aiMsg.classList.add('chatbot-message', 'ai', 'streaming');
                aiMsg.textContent = '';
                box.appendChild(aiMsg);

                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';
                let fullText = '';

                while (true) {
                    const { value, done } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop();

                    for (const line of lines) {
                        const t = line.trim();
                        if (!t || !t.startsWith('data:')) continue;
                        const payload = t.slice(5).trim();
                        if (payload === '[DONE]') continue;
                        try {
                            const evt = JSON.parse(payload);
                            if (evt.t) {
                                fullText += evt.t;
                                aiMsg.textContent = fullText;
                                box.scrollTop = box.scrollHeight;
                            } else if (evt.error) {
                                aiMsg.textContent = '⚠️ ' + evt.error;
                                aiMsg.classList.add('error');
                            }
                        } catch (e) { /* chunk parcial, ignorar */ }
                    }
                }

                aiMsg.classList.remove('streaming');
                if (!fullText.trim()) {
                    aiMsg.textContent = '(La IA no devolvió respuesta. ¿Está LM Studio corriendo en localhost:1234?)';
                    aiMsg.classList.add('error');
                }

            } catch (err) {
                if (err.name === 'AbortError') {
                    typingMsg && typingMsg.remove();
                    addMsg('⏹ Consulta cancelada.', 'ai');
                } else {
                    console.error('[Chatbot]', err);
                    typingMsg && typingMsg.remove();
                    addMsg('No pude conectar con la IA. Verifica que LM Studio esté corriendo.', 'ai error');
                }
            } finally {
                currentAbort = null;
                asSend();
                box.scrollTop = box.scrollHeight;
            }
        });

        function addMsg(text, sender) {
            const div = document.createElement('div');
            div.classList.add('chatbot-message', ...sender.split(' '));
            div.textContent = text;
            box.appendChild(div);
            box.scrollTop = box.scrollHeight;
            return div;
        }

        function addTyping() {
            const div = document.createElement('div');
            div.classList.add('chatbot-message', 'ai');
            div.innerHTML = '<div class="typing-indicator"><span></span><span></span><span></span></div>';
            box.appendChild(div);
            box.scrollTop = box.scrollHeight;
            return div;
        }

        function asCancel() {
            sendBtn.classList.add('cancel-mode');
            sendText.textContent = 'Cancelar';
            spinner.classList.remove('d-none');
        }

        function asSend() {
            sendBtn.classList.remove('cancel-mode');
            sendText.textContent = 'Enviar';
            spinner.classList.add('d-none');
        }

        console.log('[Chatbot] Asistente IA inicializado.');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>

@endif
