<!DOCTYPE html>
<html>
<head>
    <title>实时聊天室</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.0.1/socket.io.js"></script>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 10px;
        background-color: #f0f2f5;
        height: 100vh;
    }
    .chat-container {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 10px;
        height: calc(100vh - 20px); /* 全屏高度 */
        display: flex;
        flex-direction: column;
    }
    #messages {
        flex: 1;
        overflow-y: auto;
        padding: 5px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        -webkit-overflow-scrolling: touch; /* 移动端滚动优化 */
    }
    .message {
        margin: 8px 0;
        padding: 8px;
        border-radius: 8px;
        background: #f8f9fa;
        font-size: 14px;
        word-break: break-word; /* 长文本换行 */
    }
    .message.self {
        background: #007bff;
        color: white;
    }
    .notification {
        color: #4a5568;
        font-size: 12px;
        text-align: center;
        margin: 8px 0;
        padding: 5px;
    }
    .input-group {
        display: flex;
        gap: 8px;
        padding-top: 10px;
    }
    input[type="text"] {
        flex: 1;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 25px;
        font-size: 16px; /* 加大输入字体 */
    }
    button {
        padding: 12px 20px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        font-size: 16px; /* 按钮字体加大 */
        min-width: 80px; /* 保证按钮宽度 */
    }
    .timestamp {
        font-size: 10px;
        color: rgba(255,255,255,0.8);
        margin-left: 8px;
    }
    
    /* 手机横屏适配 */
    @media screen and (orientation: landscape) {
        .chat-container {
            height: calc(100vh - 20px);
        }
        #messages {
            max-height: 50vh;
        }
    }
    
    /* 小屏幕手机优化 */
    @media (max-width: 375px) {
        input[type="text"] {
            padding: 10px;
            font-size: 14px;
        }
        button {
            padding: 10px 15px;
            min-width: 70px;
        }
    }
    
    /* 点击反馈 */
    button:active {
        background: #0056b3;
        transform: scale(0.98);
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