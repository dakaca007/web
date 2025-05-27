<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>实时聊天室</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.0.1/socket.io.js"></script>
    <style>
    :root {
        --input-height: 46px;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f0f2f5;
        height: 100vh;
        -webkit-tap-highlight-color: transparent;
    }
    
    .chat-container {
        background: white;
        height: 100vh;
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }
    
    #messages {
        flex: 1;
        overflow-y: auto;
        padding: 8px 12px;
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
        overscroll-behavior: contain;
    }
    
    .message {
        margin: 10px 0;
        padding: 12px;
        border-radius: 15px;
        background: #f8f9fa;
        font-size: 16px;
        line-height: 1.4;
        max-width: 85%;
        position: relative;
        word-break: break-word;
        animation: messageAppear 0.3s ease-out;
    }
    
    .message.self {
        background: #007bff;
        color: white;
        margin-left: auto;
    }
    
    .notification {
        color: #666;
        font-size: 12px;
        text-align: center;
        margin: 12px 0;
        padding: 6px;
        background: rgba(0,0,0,0.05);
        border-radius: 20px;
    }
    
    .input-group {
        display: flex;
        gap: 8px;
        padding: 12px;
        background: #f8f9fa;
        border-top: 1px solid #eee;
        box-shadow: 0 -2px 8px rgba(0,0,0,0.03);
    }
    
    input[type="text"] {
        flex: 1;
        padding: 10px 16px;
        border: 1px solid #ddd;
        border-radius: 25px;
        font-size: 16px;
        min-height: var(--input-height);
        background: white;
        outline: none;
    }
    
    input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
    }
    
    button {
        padding: 0 20px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 25px;
        font-size: 16px;
        min-width: 80px;
        height: var(--input-height);
        transition: all 0.2s;
    }
    
    .timestamp {
        display: block;
        font-size: 12px;
        color: rgba(255,255,255,0.9);
        margin-top: 6px;
        opacity: 0.8;
    }
    
    .message:not(.self) .timestamp {
        color: rgba(0,0,0,0.6);
    }

    @keyframes messageAppear {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 480px) {
        #messages {
            padding: 6px 10px;
        }
        
        .message {
            font-size: 15px;
            max-width: 90%;
            padding: 10px;
        }
        
        input[type="text"] {
            font-size: 15px;
            padding: 8px 14px;
        }
        
        button {
            min-width: 70px;
            padding: 0 16px;
        }
    }

    @media (max-width: 375px) {
        :root {
            --input-height: 44px;
        }
        
        button {
            font-size: 15px;
            min-width: 64px;
        }
    }
    
    @media (max-width: 320px) {
        .input-group {
            padding: 8px;
        }
        
        input[type="text"] {
            font-size: 14px;
        }
    }

    /* 适配虚拟键盘 */
    @media (max-height: 420px) {
        .chat-container {
            height: 100%;
        }
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


        // 添加视口高度动态调整
        function adjustHeight() {
            const container = document.querySelector('.chat-container');
            container.style.height = window.innerHeight + 'px';
        }
        
        window.addEventListener('resize', adjustHeight);
        adjustHeight();

        // 输入框获取焦点时自动滚动到底部
        document.getElementById('messageInput').addEventListener('focus', () => {
            setTimeout(() => {
                const container = document.getElementById('messages');
                container.scrollTop = container.scrollHeight;
            }, 300);
        });
    </script>
     
</body>
</html>