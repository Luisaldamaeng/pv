document.addEventListener('DOMContentLoaded', function () {
    const bubble = document.getElementById('chatbot-bubble');
    const window = document.getElementById('chat-window');
    const closeBtn = document.getElementById('chat-close');
    const input = document.getElementById('chat-input');
    const sendBtn = document.getElementById('chat-send');
    const messagesArea = document.getElementById('chat-messages');

    // Alternar ventana de chat
    bubble.addEventListener('click', () => {
        window.classList.toggle('active');
        if (window.classList.contains('active')) {
            input.focus();
        }
    });

    closeBtn.addEventListener('click', () => {
        window.classList.remove('active');
    });

    const fileInput = document.getElementById('chat-file');
    const imageBtn = document.getElementById('chat-image');
    let selectedImageBase64 = null;
    let chatHistory = []; // Variable para guardar el historial de la sesión

    // Generar un ID de sesión único para esta pestaña si no existe
    if (!sessionStorage.getItem('chatbot_session_id')) {
        sessionStorage.setItem('chatbot_session_id', 'session_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now());
    }
    const sessionId = sessionStorage.getItem('chatbot_session_id');

    imageBtn.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                selectedImageBase64 = e.target.result.split(',')[1];
                addMessage("Imagen seleccionada lista para enviar.", 'user');
            };
            reader.readAsDataURL(file);
        }
    });

    // Enviar mensaje
    function sendMessage() {
        const text = input.value.trim();
        if (!text && !selectedImageBase64) return;

        // Añadir mensaje de usuario al historial local
        if (text) {
            addMessage(text, 'user');
            chatHistory.push({ text: text, side: 'user' });
        }
        if (selectedImageBase64) addMessage("(Imagen enviada)", 'user');

        const payload = {
            message: text,
            image: selectedImageBase64,
            history: chatHistory, // Enviamos el historial de la sesión
            session_id: sessionId // Enviamos el ID de sesión para logs
        };

        input.value = '';
        selectedImageBase64 = null;
        fileInput.value = '';

        // Mantener el historial corto para no saturar la API (últimos 10 mensajes)
        if (chatHistory.length > 10) chatHistory.shift();

        // Llamar al proxy PHP para obtener respuesta de Gemini
        fetch('chat_proxy.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
            .then(response => response.json())
            .then(data => {
                if (data.response) {
                    addMessage(data.response, 'bot');
                    chatHistory.push({ text: data.response, side: 'bot' }); // Añadir respuesta al historial
                } else if (data.error) {
                    addMessage("Lo siento, estoy teniendo dificultades técnicas. Por favor, intenta de nuevo en un momento o contacta con nosotros por WhatsApp.", "bot");
                    console.error("Chat Error:", data.error, data.details);
                }
            })
            .catch(err => {
                console.error("Error al conectar con la IA:", err);
                addMessage("Parece que hay un problema de conexión. Por favor, intenta de nuevo más tarde.", "bot");
            });
    }

    function addMessage(text, side) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `message ${side}`;
        msgDiv.textContent = text;
        messagesArea.appendChild(msgDiv);

        // Scroll al fondo
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }

    sendBtn.addEventListener('click', sendMessage);

    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // Mensaje de bienvenida inicial con menú interactivo
    setTimeout(() => {
        addMessage("¡Hola! Soy tu asistente virtual. ¿En qué puedo ayudarte hoy?", "bot");
        addOptionsMenu([
            "🕒 Horarios",
            "💳 Métodos de Pago",
            "🛵 Delivery",
            "💰 Consultar Precio"
        ]);
        addWhatsAppButton();
    }, 1000);

    function addOptionsMenu(options) {
        const optionsDiv = document.createElement('div');
        optionsDiv.className = 'options-wrapper';

        options.forEach(opt => {
            const btn = document.createElement('button');
            btn.className = 'option-btn';
            btn.textContent = opt;
            btn.onclick = () => {
                input.value = opt;
                sendMessage();
                // Opcional: eliminar el menú después de elegir
                optionsDiv.remove();
            };
            optionsDiv.appendChild(btn);
        });

        messagesArea.appendChild(optionsDiv);
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }

    function addWhatsAppButton() {
        // ...
        if (document.getElementById('wa-btn-container')) return;

        const waContainer = document.createElement('div');
        waContainer.id = 'wa-btn-container';
        waContainer.className = 'whatsapp-wrapper';
        waContainer.innerHTML = `
            <a href="https://wa.me/595992863837?text=Hola%20Luis%2C%20vengo%20de%20tu%20sitio%20web%20y%20necesito%20hablar%20con%20una%20persona." 
               target="_blank" class="whatsapp-btn">
               <span>📱</span> Hablar con un humano
            </a>
        `;
        messagesArea.appendChild(waContainer);
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }
});
