<!DOCTYPE html>

<html>
<head>
    <title>AI Chatbot</title>
<!-- Markdown Parser -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<!-- Code Highlight -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/github-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>

<style>
    body {
        font-family: Arial;
        background: #f5f5f5;
        display: flex;
        justify-content: center;
        margin-top: 0px;
    }

    #chat-container {
        width: 600px;
    }

    #chat-box {
        height: 450px;
        background: white;
        border-radius: 10px;
        padding: 15px;
        overflow-y: auto;
        border: 1px solid #ccc;
    }

    .user {
        text-align: right;
        background: #007bff;
        color: white;
        padding: 10px;
        border-radius: 10px;
        margin: 8px;
    }

    .bot {
        text-align: left;
        background: #f1f1f1;
        padding: 10px;
        border-radius: 10px;
        margin: 8px;
        line-height: 1.6;
    }

    /* Markdown styling */
    .bot h1, .bot h2, .bot h3 {
        margin-top: 10px;
    }

    .bot ul {
        padding-left: 20px;
    }

    .bot pre {
        background: #1e1e1e;
        padding: 10px;
        border-radius: 8px;
        overflow-x: auto;
    }

    .bot code {
        background: #eee;
        padding: 3px 5px;
        border-radius: 4px;
    }

    #input-area {
        margin-top: 10px;
        display: flex;
    }

    input {
        flex: 1;
        padding: 10px;
    }

    button {
        padding: 10px 15px;
    }

    #typing {
        font-style: italic;
        color: gray;
        margin: 5px;
    }
</style>

</head>

<body>

<div id="chat-container">
    <h3>AI Chatbot</h3>

<div id="chat-box">
    @foreach($chats as $chat)
        <div class="user">{{ $chat->user_message }}</div>
         <div class="bot">{!! $chat->bot_response !!}</div>
    @endforeach
</div>

<div id="input-area">
    <input type="text" id="message" placeholder="Type message..." />
    <button onclick="sendMessage()">Send</button>
</div>

</div>

<script>
function sendMessage() {
    let message = document.getElementById('message').value;
    let chatBox = document.getElementById('chat-box');

    let botId = "bot-" + Date.now();

    chatBox.innerHTML += `<div class="user">${message}</div>`;
    chatBox.innerHTML += `<div id="typing">Bot is typing...</div>`;
    chatBox.innerHTML += `<div class="bot" id="${botId}"></div>`;

    fetch('/stream-chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message: message })
    }).then(response => {
        const reader = response.body.getReader();
        const decoder = new TextDecoder("utf-8");

        let botText = "";

        function read() {
            reader.read().then(({ done, value }) => {
                if (done) {
                    let typingEl = document.getElementById('typing');
                    if (typingEl) typingEl.remove();
                    return;
                }

                let chunk = decoder.decode(value);
                let lines = chunk.split("\n");

                lines.forEach(line => {
                    if (line.includes("data: ")) {
                        let jsonStr = line.replace("data: ", "").trim();

                        if (jsonStr === "[DONE]") return;

                        try {
                            let json = JSON.parse(jsonStr);
                            let text = json.choices[0].delta.content;

                            if (text) {
                                botText += text;

                                document.getElementById(botId).innerHTML = marked.parse(botText);

                                document.querySelectorAll('pre code').forEach((el) => {
                                    hljs.highlightElement(el);
                                });

                                chatBox.scrollTop = chatBox.scrollHeight;
                            }
                        } catch (e) {}
                    }
                });

                read();
            });
        }

        read();
    });

    document.getElementById('message').value = '';
}
</script>
<script>
document.querySelectorAll('.bot').forEach(el => {
    el.innerHTML = marked.parse(el.innerHTML);
});
</script>
</body>
</html>
