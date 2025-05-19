<!DOCTYPE html>
<html>
<head>
    <title>实时聊天室</title>
    <meta charset="utf-8">
    <style>
        body {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .chat-container {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        #messageList {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #eee;
            padding: 10px;
            margin-bottom: 20px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .message .meta {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;
        }
        .message .username {
            font-weight: bold;
            color: #2c3e50;
        }
        .message .time {
            margin-left: 10px;
            color: #95a5a6;
        }
        .input-group {
            display: flex;
            gap: 10px;
        }
        input, button {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <h1>实时聊天室</h1>
    <div class="chat-container">
        <div id="messageList"></div>
        <div class="input-group">
            <input type="text" id="usernameInput" placeholder="你的名字" required>
            <input type="text" id="messageInput" placeholder="输入消息..." required>
            <button onclick="sendMessage()">发送</button>
        </div>
    </div>

    <script>

		// 登录逻辑
function login(username) {
    fetch('/flask/login', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ username })
    })
    .then(r => r.json())
    .then(data => localStorage.setItem('token', data.token));
}

// 发送请求时携带token
headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${localStorage.getItem('token')}`
}
        let eventSource;
        
        // 初始化加载历史消息
        fetch('/flask/messages?limit=20')
            .then(r => r.json())
            .then(messages => messages.forEach(addMessage));

        function connectSSE() {
            eventSource = new EventSource('/flask/sse');
            
            eventSource.onmessage = (e) => {
                const data = JSON.parse(e.data);
                addMessage(data);
            };
            
            eventSource.onerror = (e) => {
                console.error('SSE error:', e);
                setTimeout(connectSSE, 1000); // 自动重连
            };
        }

        function addMessage(msg) {
            const msgElement = document.createElement('div');
            msgElement.className = 'message';
            msgElement.innerHTML = `
                <div class="meta">
                    <span class="username">${msg.username}</span>
                    <span class="time">${msg.time}</span>
                </div>
                <div class="content">${msg.content}</div>
            `;
            
            const container = document.getElementById('messageList');
            container.appendChild(msgElement);
            container.scrollTop = container.scrollHeight;
        }

        function sendMessage() {
            const username = document.getElementById('usernameInput').value;
            const content = document.getElementById('messageInput').value;
            
            if (!username || !content) return alert('请填写用户名和消息内容');
            
            fetch('/flask/send', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ username, content })
            }).then(() => {
                document.getElementById('messageInput').value = '';
            });
        }

        // 初始化SSE连接
        connectSSE();
    </script>
</body>
</html>