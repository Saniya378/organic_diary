<!-- Organic Dairy Live Customer Support Chatbot Widget (Roman Urdu & English) -->
<style>
    :root {
        --bot-green-dark: #14281A;
        --bot-green-primary: #1F3A27;
        --bot-green-light: #2D5A3C;
        --bot-gold: #DDA15E;
        --bot-gold-hover: #BC6C25;
        --bot-cream: #FAF7F0;
        --bot-border: #E8E2D5;
        --bot-shadow: 0 14px 40px rgba(20, 40, 26, 0.25);
    }

    /* Floating Launcher Button */
    .chatbot-floating-launcher {
        position: fixed;
        bottom: 26px;
        right: 26px;
        z-index: 1050;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .chatbot-launcher-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--bot-green-light) 0%, var(--bot-green-dark) 100%);
        box-shadow: 0 8px 24px rgba(31, 58, 39, 0.35), 0 2px 8px rgba(0, 0, 0, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 26px;
        position: relative;
        border: 2px solid rgba(221, 161, 94, 0.4);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .chatbot-launcher-btn::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: inherit;
        top: 0;
        left: 0;
        z-index: -1;
        animation: botPulse 2.2s infinite cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes botPulse {
        0% { transform: scale(1); opacity: 0.7; }
        70% { transform: scale(1.35); opacity: 0; }
        100% { transform: scale(1.35); opacity: 0; }
    }

    .chatbot-floating-launcher:hover .chatbot-launcher-btn {
        transform: scale(1.08);
        box-shadow: 0 12px 32px rgba(31, 58, 39, 0.5);
    }

    .chatbot-launcher-pill {
        background: #ffffff;
        color: var(--bot-green-dark);
        font-size: 0.84rem;
        font-weight: 700;
        padding: 8px 16px;
        border-radius: 50px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        border: 1px solid var(--bot-border);
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        opacity: 0;
        transform: translateX(10px);
        transition: all 0.25s ease;
        pointer-events: none;
    }

    .chatbot-floating-launcher:hover .chatbot-launcher-pill {
        opacity: 1;
        transform: translateX(0);
    }

    .chatbot-badge-dot {
        width: 11px;
        height: 11px;
        background-color: #22c55e;
        border-radius: 50%;
        position: absolute;
        top: 2px;
        right: 2px;
        border: 2px solid #ffffff;
    }

    /* Chat Window Container */
    .chatbot-window {
        position: fixed;
        bottom: 96px;
        right: 26px;
        width: 390px;
        max-width: calc(100vw - 32px);
        height: 580px;
        max-height: calc(100vh - 120px);
        background: #ffffff;
        border-radius: 22px;
        box-shadow: var(--bot-shadow);
        border: 1px solid var(--bot-border);
        display: none;
        flex-direction: column;
        z-index: 1060;
        overflow: hidden;
        animation: botSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .chatbot-window.active {
        display: flex;
    }

    @keyframes botSlideUp {
        from { opacity: 0; transform: translateY(20px) scale(0.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Chat Header */
    .chatbot-header {
        background: linear-gradient(135deg, var(--bot-green-primary) 0%, var(--bot-green-dark) 100%);
        color: #ffffff;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2px solid var(--bot-gold);
    }

    .chatbot-header-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .chatbot-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.15);
        border: 1.5px solid var(--bot-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--bot-gold);
    }

    .chatbot-header-title {
        font-family: 'Playfair Display', serif, Georgia;
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
        color: #ffffff;
    }

    .chatbot-status-indicator {
        font-size: 0.73rem;
        color: rgba(255, 255, 255, 0.85);
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 2px;
    }

    .chatbot-status-dot {
        width: 7px;
        height: 7px;
        background: #22c55e;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 6px #22c55e;
    }

    .chatbot-header-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .chatbot-icon-btn {
        background: rgba(255, 255, 255, 0.12);
        border: none;
        color: #ffffff;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .chatbot-icon-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: scale(1.05);
    }

    /* Messages Body */
    .chatbot-body {
        flex: 1;
        background: #FAF8F5;
        padding: 16px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 14px;
        scroll-behavior: smooth;
    }

    .chatbot-msg {
        display: flex;
        flex-direction: column;
        max-width: 88%;
        animation: fadeInMsg 0.25s ease forwards;
    }

    @keyframes fadeInMsg {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .chatbot-msg-bot {
        align-self: flex-start;
    }

    .chatbot-msg-user {
        align-self: flex-end;
    }

    .chatbot-bubble {
        padding: 11px 15px;
        border-radius: 18px;
        font-size: 0.88rem;
        line-height: 1.48;
        word-wrap: break-word;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    .chatbot-msg-bot .chatbot-bubble {
        background: #ffffff;
        color: #1F2937;
        border-bottom-left-radius: 4px;
        border: 1px solid var(--bot-border);
    }

    .chatbot-msg-user .chatbot-bubble {
        background: var(--bot-green-primary);
        color: #ffffff;
        border-bottom-right-radius: 4px;
    }

    .chatbot-bubble strong {
        color: var(--bot-green-dark);
    }

    .chatbot-msg-user .chatbot-bubble strong {
        color: #ffffff;
    }

    .chatbot-bubble code {
        background: rgba(0, 0, 0, 0.06);
        padding: 2px 6px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 0.84rem;
        color: #B45309;
    }

    .chatbot-msg-user .chatbot-bubble code {
        background: rgba(255, 255, 255, 0.2);
        color: #FFFBEB;
    }

    .chatbot-time {
        font-size: 0.68rem;
        color: #9CA3AF;
        margin-top: 3px;
        padding: 0 4px;
    }

    .chatbot-msg-user .chatbot-time {
        text-align: right;
    }

    /* Language Support Banner */
    .chatbot-lang-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.72rem;
        font-weight: 600;
        background: #F3EEDB;
        color: #78350F;
        padding: 3px 10px;
        border-radius: 20px;
        margin-bottom: 6px;
    }

    /* Quick Reply Suggestion Chips */
    .chatbot-suggestions-box {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 8px;
    }

    .chatbot-chip {
        background: #ffffff;
        color: var(--bot-green-primary);
        border: 1px solid #D6CEBE;
        border-radius: 50px;
        padding: 5px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    }

    .chatbot-chip:hover {
        background: var(--bot-green-primary);
        color: #ffffff;
        border-color: var(--bot-green-primary);
        transform: translateY(-1px);
    }

    /* Action Buttons in bot message */
    .chatbot-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--bot-gold);
        color: #14281A !important;
        font-weight: 700;
        font-size: 0.8rem;
        padding: 6px 14px;
        border-radius: 50px;
        text-decoration: none;
        margin-top: 8px;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(221, 161, 94, 0.3);
    }

    .chatbot-action-btn:hover {
        background: var(--bot-gold-hover);
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    /* Product mini card */
    .chatbot-product-card {
        background: #FAF8F5;
        border: 1px solid var(--bot-border);
        border-radius: 12px;
        padding: 8px 12px;
        margin-top: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    /* Realistic Human-like Typing Animation Bubble */
    .chatbot-typing-container {
        padding: 0 16px 10px 16px;
        animation: fadeInMsg 0.2s ease;
    }

    .chatbot-typing-bubble {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        background: #ffffff;
        border-radius: 16px;
        border-bottom-left-radius: 4px;
        border: 1px solid var(--bot-border);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    .chatbot-typing-avatar {
        color: var(--bot-green-primary);
        font-size: 14px;
    }

    .chatbot-typing-text {
        font-size: 0.78rem;
        font-weight: 600;
        color: #6B7280;
    }

    .chatbot-typing-dots {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .chatbot-typing-dot {
        width: 6px;
        height: 6px;
        background: var(--bot-green-light);
        border-radius: 50%;
        animation: typingBlink 1.4s infinite ease-in-out;
    }

    .chatbot-typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .chatbot-typing-dot:nth-child(3) { animation-delay: 0.4s; }

    @keyframes typingBlink {
        0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
        40% { transform: scale(1.15); opacity: 1; }
    }

    /* Footer / Input Area */
    .chatbot-footer {
        background: #ffffff;
        padding: 10px 14px;
        border-top: 1px solid var(--bot-border);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .chatbot-input {
        flex: 1;
        border: 1px solid #E5E7EB;
        border-radius: 50px;
        padding: 9px 16px;
        font-size: 0.88rem;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        background: #F9FAFB;
    }

    .chatbot-input:focus {
        border-color: var(--bot-green-light);
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(45, 90, 60, 0.1);
    }

    .chatbot-send-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--bot-green-primary);
        border: none;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .chatbot-send-btn:hover {
        background: var(--bot-green-light);
        transform: scale(1.05);
    }

    .chatbot-send-btn:disabled {
        background: #D1D5DB;
        cursor: not-allowed;
        transform: none;
    }

    @media (max-width: 576px) {
        .chatbot-floating-launcher {
            bottom: 18px;
            right: 18px;
        }
        .chatbot-launcher-btn {
            width: 52px;
            height: 52px;
            font-size: 22px;
        }
        .chatbot-launcher-pill {
            display: none;
        }
        .chatbot-window {
            bottom: 80px;
            right: 12px;
            left: 12px;
            width: auto;
            max-width: none;
            height: 80vh;
        }
    }
</style>

<!-- Floating Launcher Trigger Button -->
<div class="chatbot-floating-launcher" id="chatbotLauncher" onclick="window.toggleSupportChat()">
    <span class="chatbot-launcher-pill">
        <i class="bi bi-chat-heart-fill text-warning"></i> Need Help? Chat with Support
    </span>
    <div class="chatbot-launcher-btn" title="Live Customer Support" aria-label="Customer Support Chatbot">
        <i class="bi bi-headset" id="chatbotLauncherIcon"></i>
        <span class="chatbot-badge-dot"></span>
    </div>
</div>

<!-- Chatbot Window Panel -->
<div class="chatbot-window" id="chatbotWindow" aria-hidden="true">
    <!-- Header -->
    <div class="chatbot-header">
        <div class="chatbot-header-info">
            <div class="chatbot-avatar">
                <i class="bi bi-robot"></i>
            </div>
            <div>
                <h6 class="chatbot-header-title">Organic Dairy Support</h6>
                <div class="chatbot-status-indicator">
                    <span class="chatbot-status-dot"></span>
                    <span>Ayla &bull; Roman Urdu &amp; English</span>
                </div>
            </div>
        </div>
        <div class="chatbot-header-actions">
            <button class="chatbot-icon-btn" onclick="window.clearSupportChat()" title="Reset Conversation" aria-label="Reset Conversation">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
            <button class="chatbot-icon-btn" onclick="window.toggleSupportChat()" title="Close Chat" aria-label="Close Chat">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <!-- Messages List -->
    <div class="chatbot-body" id="chatbotMessages">
        <!-- Initial Bot Greeting -->
        <div class="chatbot-msg chatbot-msg-bot">
            <div class="chatbot-bubble">
                <div class="chatbot-lang-badge">
                    <i class="bi bi-translate"></i> Roman Urdu &amp; English Supported
                </div>
                Assalam-o-Alaikum! 🏔️ Welcome to <strong>Organic Dairy Gilgit-Baltistan</strong>.<br><br>
                Main <strong>Ayla</strong> hoon, aapki AI customer support assistant. Aap mujh se <strong>Roman Urdu</strong> ya <strong>English</strong> me taza doodh, order tracking, ya rozana deals ke baray me kuch bhi pooch sakte hain!
            </div>
            <div class="chatbot-time">Just now</div>
            <div class="chatbot-suggestions-box">
                <button class="chatbot-chip" onclick="window.sendSupportChip('Order track karna hai')">📦 Order Track Karein</button>
                <button class="chatbot-chip" onclick="window.sendSupportChip('Taza doodh aur ghee ke rates kya hain?')">🥛 Doodh &amp; Ghee Rates</button>
                <button class="chatbot-chip" onclick="window.sendSupportChip('Delivery kahan kahan hoti hai?')">🚚 Delivery Areas</button>
                <button class="chatbot-chip" onclick="window.sendSupportChip('Rozana doodh ki subscriptions deals')">🎁 Milk Subscriptions</button>
                <button class="chatbot-chip" onclick="window.sendSupportChip('Farmer seller kaise banein?')">🧑‍🌾 Farmer Registration</button>
            </div>
        </div>
    </div>

    <!-- Realistic Human Typing Indicator (Hidden by default) -->
    <div class="chatbot-typing-container" id="chatbotTyping" style="display: none;">
        <div class="chatbot-typing-bubble">
            <span class="chatbot-typing-avatar"><i class="bi bi-robot"></i></span>
            <span class="chatbot-typing-text">Ayla is typing</span>
            <span class="chatbot-typing-dots">
                <span class="chatbot-typing-dot"></span>
                <span class="chatbot-typing-dot"></span>
                <span class="chatbot-typing-dot"></span>
            </span>
        </div>
    </div>

    <!-- Footer Input Form -->
    <form class="chatbot-footer" id="chatbotForm" onsubmit="window.handleSupportSubmit(event)">
        <input 
            type="text" 
            class="chatbot-input" 
            id="chatbotInput" 
            placeholder="Type in Roman Urdu or English..." 
            autocomplete="off"
            aria-label="Ask support question"
        />
        <button type="submit" class="chatbot-send-btn" id="chatbotSendBtn" aria-label="Send Message">
            <i class="bi bi-send-fill"></i>
        </button>
    </form>
</div>

<script>
    (function () {
        const chatWindow = document.getElementById('chatbotWindow');
        const launcherIcon = document.getElementById('chatbotLauncherIcon');
        const messagesContainer = document.getElementById('chatbotMessages');
        const chatInput = document.getElementById('chatbotInput');
        const sendBtn = document.getElementById('chatbotSendBtn');
        const typingIndicator = document.getElementById('chatbotTyping');

        const STORAGE_KEY = 'od_support_chat_history_v2';

        // Toggle chat window open / close
        window.toggleSupportChat = function () {
            const isActive = chatWindow.classList.toggle('active');
            chatWindow.setAttribute('aria-hidden', !isActive);
            if (isActive) {
                launcherIcon.classList.remove('bi-headset');
                launcherIcon.classList.add('bi-chevron-down');
                setTimeout(() => chatInput.focus(), 200);
                scrollMessagesToBottom();
            } else {
                launcherIcon.classList.remove('bi-chevron-down');
                launcherIcon.classList.add('bi-headset');
            }
        };

        // Open chat with a specific query and submit it automatically
        window.openSupportChat = function (queryText) {
            if (!chatWindow.classList.contains('active')) {
                window.toggleSupportChat();
            }
            if (queryText) {
                chatInput.value = queryText;
                window.sendSupportMessage(queryText);
            }
        };

        // Clear chat history
        window.clearSupportChat = function () {
            localStorage.removeItem(STORAGE_KEY);
            messagesContainer.innerHTML = `
                <div class="chatbot-msg chatbot-msg-bot">
                    <div class="chatbot-bubble">
                        <div class="chatbot-lang-badge">
                            <i class="bi bi-translate"></i> Roman Urdu &amp; English Supported
                        </div>
                        Assalam-o-Alaikum! 🏔️ Welcome to <strong>Organic Dairy Gilgit-Baltistan</strong>.<br><br>
                        Main <strong>Ayla</strong> hoon, aapki AI customer support assistant. Aap mujh se <strong>Roman Urdu</strong> ya <strong>English</strong> me taza doodh, order tracking, ya rozana deals ke baray me kuch bhi pooch sakte hain!
                    </div>
                    <div class="chatbot-time">Just now</div>
                    <div class="chatbot-suggestions-box">
                        <button class="chatbot-chip" onclick="window.sendSupportChip('Order track karna hai')">📦 Order Track Karein</button>
                        <button class="chatbot-chip" onclick="window.sendSupportChip('Taza doodh aur ghee ke rates kya hain?')">🥛 Doodh &amp; Ghee Rates</button>
                        <button class="chatbot-chip" onclick="window.sendSupportChip('Delivery kahan kahan hoti hai?')">🚚 Delivery Areas</button>
                        <button class="chatbot-chip" onclick="window.sendSupportChip('Rozana doodh ki subscriptions deals')">🎁 Milk Subscriptions</button>
                        <button class="chatbot-chip" onclick="window.sendSupportChip('Farmer seller kaise banein?')">🧑‍🌾 Farmer Registration</button>
                    </div>
                </div>
            `;
            scrollMessagesToBottom();
        };

        // Send message from chip
        window.sendSupportChip = function (chipText) {
            window.sendSupportMessage(chipText);
        };

        // Form submit handler
        window.handleSupportSubmit = function (e) {
            e.preventDefault();
            const text = chatInput.value.trim();
            if (text) {
                window.sendSupportMessage(text);
            }
        };

        // Core Send Message function with realistic human-like typing delay
        window.sendSupportMessage = async function (userText) {
            if (!userText) return;

            // Render User Message
            renderMessage(userText, 'user');
            chatInput.value = '';
            chatInput.disabled = true;
            sendBtn.disabled = true;

            // Show Typing Indicator immediately
            showTyping(true);

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                const responsePromise = fetch("{{ route('chatbot.message') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: userText })
                });

                const [response] = await Promise.all([
                    responsePromise,
                    // Ensure typing indicator shows for at least 1.4 seconds
                    new Promise(resolve => setTimeout(resolve, 1400))
                ]);

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();

                // Dynamic extra delay based on reply length (up to 600ms extra for long replies)
                const textLength = (data.reply || '').length;
                const extraDelay = Math.min(600, Math.floor(textLength * 2));
                if (extraDelay > 0) {
                    await new Promise(resolve => setTimeout(resolve, extraDelay));
                }

                showTyping(false);
                renderBotResponse(data);
                saveChatHistory();
            } catch (err) {
                console.error('Chatbot error:', err);
                showTyping(false);
                renderMessage("I'm sorry, connection me masla aa gaya. Aap hamari helpline **+92 5811 455000** par call kar sakte hain ya dubara try karein.", 'bot');
            } finally {
                chatInput.disabled = false;
                sendBtn.disabled = false;
                chatInput.focus();
                scrollMessagesToBottom();
            }
        };

        function showTyping(show) {
            typingIndicator.style.display = show ? 'block' : 'none';
            if (show) scrollMessagesToBottom();
        }

        function scrollMessagesToBottom() {
            setTimeout(() => {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }, 50);
        }

        function getTimeString() {
            const now = new Date();
            return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        function formatMarkdown(text) {
            if (!text) return '';
            let html = text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/`([^`]+)`/g, '<code>$1</code>')
                .replace(/\n\n/g, '<br><br>')
                .replace(/\n/g, '<br>');
            return html;
        }

        function renderMessage(content, sender) {
            const time = getTimeString();
            const msgEl = document.createElement('div');
            msgEl.className = `chatbot-msg chatbot-msg-${sender}`;

            const formatted = formatMarkdown(content);
            msgEl.innerHTML = `
                <div class="chatbot-bubble">${formatted}</div>
                <div class="chatbot-time">${time}</div>
            `;

            messagesContainer.appendChild(msgEl);
            scrollMessagesToBottom();
        }

        function renderBotResponse(data) {
            const time = getTimeString();
            const msgEl = document.createElement('div');
            msgEl.className = 'chatbot-msg chatbot-msg-bot';

            let bodyHtml = `<div class="chatbot-bubble">${formatMarkdown(data.reply || '')}`;

            // Render Product Cards if any
            if (data.products && Array.isArray(data.products)) {
                data.products.forEach(p => {
                    bodyHtml += `
                        <div class="chatbot-product-card">
                            <div>
                                <div class="fw-bold text-dark small">${p.name}</div>
                                <div class="text-xs text-muted">${p.price} / ${p.unit} &bull; ${p.farm}</div>
                            </div>
                            <a href="${p.url}" class="btn btn-sm btn-dark text-white rounded-pill px-2.5 py-1 text-xs fw-semibold">View</a>
                        </div>
                    `;
                });
            }

            // Render Action Buttons
            if (data.actions && Array.isArray(data.actions)) {
                bodyHtml += `<div class="mt-2">`;
                data.actions.forEach(a => {
                    bodyHtml += `
                        <a href="${a.url}" class="chatbot-action-btn">
                            <i class="bi ${a.icon || 'bi-arrow-right'}"></i> ${a.label}
                        </a>
                    `;
                });
                bodyHtml += `</div>`;
            }

            bodyHtml += `</div><div class="chatbot-time">${time}</div>`;

            // Render Suggestion Chips
            if (data.suggestions && Array.isArray(data.suggestions)) {
                bodyHtml += `<div class="chatbot-suggestions-box">`;
                data.suggestions.forEach(chip => {
                    bodyHtml += `<button class="chatbot-chip" onclick="window.sendSupportChip('${chip.replace(/'/g, "\\'")}')">${chip}</button>`;
                });
                bodyHtml += `</div>`;
            }

            msgEl.innerHTML = bodyHtml;
            messagesContainer.appendChild(msgEl);
            scrollMessagesToBottom();
        }

        function saveChatHistory() {
            try {
                localStorage.setItem(STORAGE_KEY, messagesContainer.innerHTML);
            } catch (e) {}
        }

        // Restore history if available
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved && saved.trim() !== '') {
                messagesContainer.innerHTML = saved;
            }
        } catch (e) {}

    })();
</script>
