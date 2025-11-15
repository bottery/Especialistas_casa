// Sistema de Chat en Tiempo Real
class RealtimeChat {
    constructor(options = {}) {
        this.options = {
            pollInterval: options.pollInterval || 5000, // 5 segundos para chat
            apiEndpoint: options.apiEndpoint || '/api/chat',
            onNewMessage: options.onNewMessage || (() => {}),
            onTyping: options.onTyping || (() => {}),
            maxMessages: options.maxMessages || 100
        };

        this.currentChatId = null;
        this.messages = new Map(); // chatId -> messages[]
        this.isPolling = false;
        this.pollTimer = null;
        this.typingTimer = null;
        this.isTyping = false;
    }

    // Iniciar chat con una solicitud específica
    async startChat(solicitudId, participantId) {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.options.apiEndpoint}/start`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ solicitud_id: solicitudId, participant_id: participantId })
            });

            if (!response.ok) throw new Error('Failed to start chat');

            const data = await response.json();
            this.currentChatId = data.chat_id;
            
            // Cargar mensajes existentes
            await this.loadMessages();
            
            // Iniciar polling
            this.startPolling();

            return this.currentChatId;
        } catch (error) {
            console.error('[Chat] Start error:', error);
            throw error;
        }
    }

    async loadMessages() {
        if (!this.currentChatId) return;

        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.options.apiEndpoint}/${this.currentChatId}/messages`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            if (!response.ok) throw new Error('Failed to load messages');

            const data = await response.json();
            this.messages.set(this.currentChatId, data.messages || []);
            
            return data.messages;
        } catch (error) {
            console.error('[Chat] Load messages error:', error);
            return [];
        }
    }

    async sendMessage(text, attachments = []) {
        if (!this.currentChatId || !text.trim()) return;

        try {
            const token = localStorage.getItem('token');
            const formData = new FormData();
            formData.append('text', text);
            formData.append('chat_id', this.currentChatId);
            
            attachments.forEach((file, index) => {
                formData.append(`attachment_${index}`, file);
            });

            const response = await fetch(`${this.options.apiEndpoint}/send`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` },
                body: formData
            });

            if (!response.ok) throw new Error('Failed to send message');

            const data = await response.json();
            
            // Agregar mensaje a la lista local
            const messages = this.messages.get(this.currentChatId) || [];
            messages.push(data.message);
            this.messages.set(this.currentChatId, messages);

            // Callback
            this.options.onNewMessage(data.message);

            return data.message;
        } catch (error) {
            console.error('[Chat] Send error:', error);
            throw error;
        }
    }

    startPolling() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        this.poll();
    }

    stopPolling() {
        this.isPolling = false;
        if (this.pollTimer) {
            clearTimeout(this.pollTimer);
            this.pollTimer = null;
        }
    }

    async poll() {
        if (!this.isPolling || !this.currentChatId) return;

        try {
            const token = localStorage.getItem('token');
            const messages = this.messages.get(this.currentChatId) || [];
            const lastMessageId = messages.length > 0 ? messages[messages.length - 1].id : 0;

            const response = await fetch(
                `${this.options.apiEndpoint}/${this.currentChatId}/poll?since=${lastMessageId}`,
                { headers: { 'Authorization': `Bearer ${token}` } }
            );

            if (!response.ok) throw new Error('Poll failed');

            const data = await response.json();

            // Nuevos mensajes
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    messages.push(msg);
                    this.options.onNewMessage(msg);
                });
                this.messages.set(this.currentChatId, messages);
            }

            // Estado de escritura
            if (data.typing) {
                this.options.onTyping(data.typing);
            }

        } catch (error) {
            console.error('[Chat] Poll error:', error);
        }

        // Siguiente poll
        if (this.isPolling) {
            this.pollTimer = setTimeout(() => this.poll(), this.options.pollInterval);
        }
    }

    async sendTypingIndicator(isTyping) {
        if (!this.currentChatId) return;

        try {
            const token = localStorage.getItem('token');
            await fetch(`${this.options.apiEndpoint}/${this.currentChatId}/typing`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ is_typing: isTyping })
            });
        } catch (error) {
            console.error('[Chat] Typing indicator error:', error);
        }
    }

    handleTyping() {
        if (!this.isTyping) {
            this.isTyping = true;
            this.sendTypingIndicator(true);
        }

        // Reset timer
        clearTimeout(this.typingTimer);
        this.typingTimer = setTimeout(() => {
            this.isTyping = false;
            this.sendTypingIndicator(false);
        }, 3000);
    }

    getMessages() {
        return this.messages.get(this.currentChatId) || [];
    }

    clearChat() {
        this.stopPolling();
        this.currentChatId = null;
    }

    // Crear componente de chat UI
    createChatWidget(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = `
            <div class="chat-widget fixed bottom-4 right-4 w-96 bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden z-50 flex flex-col" style="height: 600px; max-height: 80vh;">
                <!-- Header -->
                <div class="chat-header bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                        <h3 class="text-white font-semibold">Chat en Vivo</h3>
                    </div>
                    <button onclick="window.realtimeChat.toggleWidget()" class="text-white hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Messages -->
                <div id="chat-messages" class="chat-messages flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50 dark:bg-gray-900">
                    <div class="text-center text-gray-500 dark:text-gray-400 text-sm py-8">
                        No hay mensajes aún. ¡Envía el primero!
                    </div>
                </div>

                <!-- Typing indicator -->
                <div id="typing-indicator" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 hidden">
                    <span class="inline-flex items-center gap-1">
                        <span class="typing-dot"></span>
                        <span class="typing-dot"></span>
                        <span class="typing-dot"></span>
                        <span class="ml-1">Escribiendo...</span>
                    </span>
                </div>

                <!-- Input -->
                <div class="chat-input border-t border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex gap-2">
                        <input type="text" 
                               id="chat-input" 
                               placeholder="Escribe un mensaje..."
                               class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                               onkeypress="if(event.key==='Enter') window.realtimeChat.sendMessageFromInput()"
                               oninput="window.realtimeChat.handleTyping()">
                        <button onclick="window.realtimeChat.sendMessageFromInput()" 
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <style>
                .typing-dot {
                    width: 6px;
                    height: 6px;
                    background: currentColor;
                    border-radius: 50%;
                    display: inline-block;
                    animation: typing 1.4s infinite ease-in-out;
                }
                .typing-dot:nth-child(2) { animation-delay: 0.2s; }
                .typing-dot:nth-child(3) { animation-delay: 0.4s; }
                @keyframes typing {
                    0%, 60%, 100% { transform: translateY(0); }
                    30% { transform: translateY(-10px); }
                }
            </style>
        `;

        // Setup event handlers
        this.setupChatUI();
    }

    setupChatUI() {
        // Listen for new messages
        this.options.onNewMessage = (message) => {
            this.appendMessageToUI(message);
        };

        this.options.onTyping = (typing) => {
            const indicator = document.getElementById('typing-indicator');
            if (indicator) {
                indicator.classList.toggle('hidden', !typing.is_typing);
            }
        };
    }

    appendMessageToUI(message) {
        const messagesContainer = document.getElementById('chat-messages');
        if (!messagesContainer) return;

        const userId = JSON.parse(localStorage.getItem('usuario') || '{}').id;
        const isOwn = message.user_id === userId;

        const messageEl = document.createElement('div');
        messageEl.className = `flex ${isOwn ? 'justify-end' : 'justify-start'}`;
        messageEl.innerHTML = `
            <div class="max-w-[70%] ${isOwn ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800'} rounded-lg px-4 py-2 shadow">
                ${!isOwn ? `<p class="text-xs font-semibold mb-1 ${isOwn ? 'text-indigo-200' : 'text-gray-600 dark:text-gray-400'}">${message.user_name}</p>` : ''}
                <p class="${isOwn ? 'text-white' : 'text-gray-900 dark:text-gray-100'}">${this.escapeHtml(message.text)}</p>
                <p class="text-xs mt-1 ${isOwn ? 'text-indigo-200' : 'text-gray-500 dark:text-gray-400'}">${this.formatTime(message.created_at)}</p>
            </div>
        `;

        // Remove empty state
        const emptyState = messagesContainer.querySelector('.text-center');
        if (emptyState) emptyState.remove();

        messagesContainer.appendChild(messageEl);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    sendMessageFromInput() {
        const input = document.getElementById('chat-input');
        if (!input || !input.value.trim()) return;

        this.sendMessage(input.value.trim());
        input.value = '';
    }

    toggleWidget() {
        const widget = document.querySelector('.chat-widget');
        if (widget) {
            widget.classList.toggle('hidden');
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    }
}

// Instancia global
window.realtimeChat = new RealtimeChat();
