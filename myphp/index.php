<!DOCTYPE html>
<html>
<head>
    <title>实时聊天室</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.0.1/socket.io.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f0f2f5;
        }
        .chat-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        #messages {
            height: 60vh;
            overflow-y: auto;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            background: #f8f9fa;
        }
        .message.self {
            background: #007bff;
            color: white;
        }
        .notification {
            color: #6c757d;
            font-size: 0.9em;
            text-align: center;
            margin: 10px 0;
        }
        .input-group {
            display: flex;
            gap: 10px;
        }
        input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .timestamp {
            font-size: 0.8em;
            color: #666;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div id="messages"></div>
        <div class="input-group">
            <input type="text" id="messageInput" placeholder="输入消息...">
            <button onclick="sendMessage()">发送</button>
        </div>
    </div>

    <script>
        let nickname = null;
        const socket = io.connect('https://' + document.domain, {
            path: '/flask/socket.io'
        });

        // 设置昵称
        while(!nickname) {
            nickname = prompt('请输入您的昵称:')?.trim();
            if(nickname) {
                socket.emit('set_nickname', { nickname });
            }
        }

        // 处理服务器消息
        socket.on('server_response', function(data) {
            const container = document.getElementById('messages');
            
            const div = document.createElement('div');
            div.innerHTML = formatMessage(data);
            
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
        });

        // 格式化消息
        function formatMessage(data) {
            const date = new Date(data.timestamp * 1000);
            const timeString = date.toLocaleTimeString();
            
            switch(data.type) {
                case 'message':
                    const isSelf = data.nickname === nickname;
                    return `
                        <div class="message ${isSelf ? 'self' : ''}">
                            <strong>${data.nickname}</strong>
                            <span class="timestamp">${timeString}</span>
                            <div>${data.content}</div>
                        </div>
                    `;
                
                case 'notification':
                case 'system':
                    return `
                        <div class="notification">
                            ${data.data} 
                            <span class="timestamp">${timeString}</span>
                        </div>
                    `;
                
                case 'error':
                    return `
                        <div class="notification" style="color: red;">
                            ${data.data}
                        </div>
                    `;
            }
        }

        // 发送消息
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const content = input.value.trim();
            
            if(content) {
                socket.emit('client_message', { content });
                input.value = '';
            }
        }

        // 回车发送消息
        document.getElementById('messageInput').addEventListener('keypress', (e) => {
            if(e.key === 'Enter') {
                sendMessage();
            }
        });
    </script>
</body>
</html>