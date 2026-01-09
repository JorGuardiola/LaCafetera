<div id="ai-chat-container">
    
    <div id="chat-content" class="chat-content">
        <div class="chat-header">
            Barista IA - Carta y Consultas
        </div>
        
        <div id="chat-window" class="chat-window">
            <p><strong>Barista:</strong> ¡Hola! ¿Quieres ver nuestra lista de cafés? También puedes preguntarme por nuestro horario o ubicación.</p>
        </div>

        <div class="chat-input-area">
            <input type="text" id="user-input" placeholder="Escribe 'lista'...">
            <button onclick="askAI()">Enviar</button>
        </div>
    </div>

    <div class="button-container">
        <button onclick="toggleChat()" class="chat-toggle-button">
            Habla con nuestro Barista
        </button>
    </div>
</div>

<script>
function toggleChat() {
    const chat = document.getElementById('chat-content');
    chat.style.display = (chat.style.display === 'none' || chat.style.display === '') ? 'block' : 'none';
}

async function askAI() {
    const input = document.getElementById('user-input');
    const windowChat = document.getElementById('chat-window');
    const text = input.value.trim();
    
    if(!text) return;

    // Mensaje del usuario
    windowChat.innerHTML += `<div style="margin-bottom:15px;"><strong>Tú:</strong> ${text}</div>`;
    input.value = '';
    windowChat.scrollTop = windowChat.scrollHeight;

    try {
        const res = await fetch('chat_handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text })
        });
        
        const data = await res.json();
        const reply = data.choices[0].message.content;
        
        // Respuesta del Barista
        windowChat.innerHTML += `<div style="color:#6F4E37; margin-bottom:20px; background:#f0ebe5; padding:10px; border-radius:8px;">
            <strong>Barista:</strong><br> ${reply}
        </div>`;
        
    } catch (error) {
        windowChat.innerHTML += `<p style="color:red;"><strong>Error:</strong> No pude conectar con el servidor.</p>`;
    }
    windowChat.scrollTop = windowChat.scrollHeight;
}

document.getElementById('user-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') askAI();
});
</script>