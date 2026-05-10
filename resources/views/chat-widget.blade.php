<style>
#kira-chat-btn {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 1050;
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: #556ee6;
    border: none;
    box-shadow: 0 4px 14px rgba(85,110,230,.45);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .2s;
}
#kira-chat-btn:hover { background: #485ec4; }

#kira-chat-panel {
    position: fixed;
    bottom: 94px;
    right: 28px;
    z-index: 1050;
    width: 350px;
    height: 480px;
    display: none;
    flex-direction: column;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,.18);
    overflow: hidden;
    background: #fff;
}
#kira-chat-panel.open { display: flex; }

#kira-chat-header {
    background: #556ee6;
    color: #fff;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
    font-size: 15px;
    flex-shrink: 0;
}
#kira-chat-close {
    background: none;
    border: none;
    color: #fff;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
    padding: 0 2px;
    opacity: .8;
}
#kira-chat-close:hover { opacity: 1; }
#kira-chat-clear {
    background: none;
    border: 1px solid rgba(255,255,255,.5);
    color: #fff;
    font-size: 12px;
    line-height: 1;
    cursor: pointer;
    padding: 3px 8px;
    border-radius: 4px;
    opacity: .8;
}
#kira-chat-clear:hover { opacity: 1; }

#kira-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #f8f9fa;
}

.kira-bubble {
    max-width: 80%;
    padding: 9px 13px;
    border-radius: 16px;
    font-size: 14px;
    line-height: 1.45;
    word-break: break-word;
}
.kira-bubble.user {
    align-self: flex-end;
    background: #556ee6;
    color: #fff;
    border-bottom-right-radius: 4px;
}
.kira-bubble.bot {
    align-self: flex-start;
    background: #fff;
    color: #333;
    border: 1px solid #e9ecef;
    border-bottom-left-radius: 4px;
}
.kira-bubble.error {
    align-self: flex-start;
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffc107;
    border-bottom-left-radius: 4px;
}
.kira-bubble.bot p { margin: 0 0 6px 0; }
.kira-bubble.bot p:last-child { margin-bottom: 0; }
.kira-bubble.bot ul, .kira-bubble.bot ol { margin: 4px 0; padding-left: 18px; }
.kira-bubble.bot li { margin-bottom: 2px; }
.kira-bubble.bot code { background: #f0f0f0; padding: 1px 4px; border-radius: 3px; font-size: 12px; }
.kira-bubble.bot strong { font-weight: 600; }

#kira-chat-footer {
    display: flex;
    gap: 8px;
    padding: 12px;
    border-top: 1px solid #e9ecef;
    background: #fff;
    flex-shrink: 0;
}
#kira-chat-input {
    flex: 1;
    border: 1px solid #ced4da;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 14px;
    outline: none;
    transition: border-color .15s;
}
#kira-chat-input:focus { border-color: #556ee6; }
#kira-chat-send {
    background: #556ee6;
    border: none;
    border-radius: 8px;
    color: #fff;
    padding: 8px 14px;
    font-size: 14px;
    cursor: pointer;
    transition: background .2s;
    white-space: nowrap;
}
#kira-chat-send:hover:not(:disabled) { background: #485ec4; }
#kira-chat-send:disabled,
#kira-chat-input:disabled { opacity: .6; cursor: not-allowed; }
</style>

{{-- Toggle button --}}
<button id="kira-chat-btn" aria-label="Open Kira Assistant">
    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="#fff" viewBox="0 0 24 24">
        <path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/>
    </svg>
</button>

{{-- Chat panel --}}
<div id="kira-chat-panel" role="dialog" aria-label="Kira Assistant">
    <div id="kira-chat-header">
        <span>Kira Assistant</span>
        <div style="display:flex;gap:8px;align-items:center;">
            <button id="kira-chat-clear" aria-label="Clear conversation">Clear</button>
            <button id="kira-chat-close" aria-label="Close">&times;</button>
        </div>
    </div>
    <div id="kira-chat-messages">
        <div id="kira-starter-chips" style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;padding:12px 8px;">
            <button class="btn btn-sm btn-outline-secondary kira-chip" type="button">Show me overdue invoices</button>
            <button class="btn btn-sm btn-outline-secondary kira-chip" type="button">What&#39;s this month&#39;s revenue?</button>
            <button class="btn btn-sm btn-outline-secondary kira-chip" type="button">How do I create a credit note?</button>
        </div>
    </div>
    <div id="kira-chat-footer">
        <input id="kira-chat-input" type="text" placeholder="Ask anything..." autocomplete="off" />
        <button id="kira-chat-send">Send</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
(function () {
    var panel  = document.getElementById('kira-chat-panel');
    var btn    = document.getElementById('kira-chat-btn');
    var close  = document.getElementById('kira-chat-close');
    var input  = document.getElementById('kira-chat-input');
    var send   = document.getElementById('kira-chat-send');
    var thread = document.getElementById('kira-chat-messages');

    var clearBtn     = document.getElementById('kira-chat-clear');
    var starterChips = document.getElementById('kira-starter-chips');
    var chatUrl   = "{{ route('chat.send') }}";
    var clearUrl  = "{{ route('chat.clear') }}";
    var csrfToken = "{{ csrf_token() }}";

    document.querySelectorAll('.kira-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            input.value = chip.textContent;
            sendMessage();
        });
    });

    clearBtn.addEventListener('click', function () {
        fetch(clearUrl, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        }).then(function () {
            thread.innerHTML = '';
            var info = document.createElement('div');
            info.className = 'kira-bubble bot';
            info.style.fontSize = '12px';
            info.style.opacity = '.7';
            info.textContent = 'Conversation cleared.';
            thread.appendChild(info);
        });
    });

    btn.addEventListener('click', function () {
        panel.classList.toggle('open');
        if (panel.classList.contains('open')) { input.focus(); }
    });

    close.addEventListener('click', function () {
        panel.classList.remove('open');
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { sendMessage(); }
    });

    send.addEventListener('click', sendMessage);

    function appendBubble(text, type) {
        var div = document.createElement('div');
        div.className = 'kira-bubble ' + type;
        div.textContent = text;
        thread.appendChild(div);
        scrollBottom();
        return div;
    }

    function scrollBottom() {
        thread.scrollTop = thread.scrollHeight;
    }

    function setDisabled(state) {
        input.disabled = state;
        send.disabled  = state;
    }

    function sendMessage() {
        var text = input.value.trim();
        if (!text) { return; }

        if (starterChips) { starterChips.style.display = 'none'; }
        appendBubble(text, 'user');
        input.value = '';
        setDisabled(true);

        var botBubble = null;
        var rawBuffer = '';
        var buffer = '';

        fetch(chatUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'text/event-stream',
            },
            body: JSON.stringify({ message: text }),
        })
        .then(function (res) {
            if (!res.ok) { throw new Error('bad response'); }
            var reader = res.body.getReader();
            var decoder = new TextDecoder();

            function read() {
                return reader.read().then(function (result) {
                    if (result.done) {
                        setDisabled(false);
                        scrollBottom();
                        input.focus();
                        return;
                    }

                    buffer += decoder.decode(result.value, { stream: true });
                    var parts = buffer.split('\n\n');
                    buffer = parts.pop();

                    parts.forEach(function (part) {
                        if (!part.startsWith('data: ')) { return; }
                        var data = part.slice(6);
                        if (data === '[DONE]') {
                            if (botBubble) { botBubble.innerHTML = marked.parse(rawBuffer); }
                            setDisabled(false);
                            scrollBottom();
                            input.focus();
                            return;
                        }
                        try {
                            var parsed = JSON.parse(data);
                            if (parsed.token !== undefined) {
                                rawBuffer += parsed.token;
                                if (!botBubble) {
                                    botBubble = appendBubble('', 'bot');
                                }
                                botBubble.textContent += parsed.token;
                                scrollBottom();
                            } else if (parsed.error) {
                                appendBubble(parsed.error, 'error');
                            }
                        } catch (e) {}
                    });

                    return read();
                });
            }

            return read();
        })
        .catch(function () {
            appendBubble('Sorry, something went wrong.', 'error');
            setDisabled(false);
            input.focus();
        });
    }
}());
</script>
