<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <title>实时聊天室</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
        :root {
            --primary-color: #2196F3;
            --background-color: #f5f5f5;
            --card-background: #ffffff;
            --text-color: #333333;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0 0 80px;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .auth-box {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: var(--card-background);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .auth-form input {
            width: 100%;
            margin-bottom: 15px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .auth-actions {
            display: flex;
            gap: 10px;
        }

        .auth-actions button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .auth-actions button:first-child {
            background-color: var(--primary-color);
            color: white;
        }

        .auth-actions button:last-child {
            background-color: #f0f0f0;
            color: var(--text-color);
        }

        #onlineStatus {
            position: fixed;
            top: 10px;
            right: 10px;
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 1000;
            max-width: 200px;
        }

        #onlineStatus h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
        }

        #onlineList {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 150px;
            overflow-y: auto;
        }

        #onlineList li {
            padding: 2px 0;
            font-size: 12px;
        }

        #sendForm {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--card-background);
            padding: 15px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        #messageInput {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s ease;
            min-height: 50px;
            box-sizing: border-box;
        }

        #messageInput:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 8px rgba(33, 150, 243, 0.2);
        }

        button[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            min-width: 80px;
            flex-shrink: 0;
        }

        button[type="submit"]:hover {
            background-color: #1976D2;
            transform: translateY(-1px);
        }

        #chatLog {
            height: calc(100vh - 160px);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 10px 0;
        }

        .message-item {
            margin: 8px 0;
            padding: 12px 18px;
            background: var(--card-background);
            border-radius: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            word-break: break-word;
            animation: fadeIn 0.3s ease;
            position: relative;
        }

        .mention {
            background-color: #fff3d6;
            border-left: 3px solid #ffd54f;
            padding-left: 8px;
        }

        .message-item strong {
            color: var(--primary-color);
            margin-right: 8px;
        }

        .message-item small {
            color: #666;
            font-size: 11px;
            position: absolute;
            bottom: 4px;
            right: 12px;
        }

        #fileUpload {
            position: fixed;
            bottom: 80px;
            right: 20px;
            z-index: 1000;
        }

        #fileUpload button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
        }

        #preview {
            margin-top: 10px;
            max-width: 200px;
        }

        #preview img {
            max-width: 100%;
            border-radius: 5px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 600px) {
            body {
                padding-bottom: 70px;
            }

            .container {
                padding: 10px;
            }

            #sendForm {
                padding: 10px;
                gap: 8px;
            }

            #messageInput {
                padding: 10px 14px;
                font-size: 14px;
                min-height: 44px;
            }

            button[type="submit"] {
                padding: 10px 16px;
                font-size: 14px;
            }

            #chatLog {
                height: calc(100vh - 140px);
            }

            #onlineStatus {
                position: static;
                margin: 10px;
                max-width: none;
            }
        }

        .error-message {
            color: #f44336;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 3px solid #f44336;
        }

        .success-message {
            color: #4CAF50;
            background-color: #e8f5e8;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 3px solid #4CAF50;
        }
    </style>
</head>
<body>
    <div id="onlineStatus" style="display: none;">
        <h3>在线用户 (<span id="onlineCount">0</span>)</h3>
        <ul id="onlineList"></ul>
    </div>

    <div class="auth-box" id="authBox">
        <div id="loginForm" class="auth-form">
            <h2>用户登录</h2>
            <div id="loginMessage"></div>
            <input type="text" id="loginUser" placeholder="用户名" required>
            <input type="password" id="loginPass" placeholder="密码" required>
            <div class="auth-actions">
                <button type="button" onclick="login()">登录</button>
                <button type="button" onclick="showRegister()">注册</button>
            </div>
        </div>

        <div id="registerForm" class="auth-form" style="display: none;">
            <h2>用户注册</h2>
            <div id="registerMessage"></div>
            <input type="text" id="regUser" placeholder="用户名（3-20字符）" required>
            <input type="password" id="regPass" placeholder="密码（至少6位）" required>
            <div class="auth-actions">
                <button type="button" onclick="register()">注册</button>
                <button type="button" onclick="showLogin()">返回登录</button>
            </div>
        </div>
    </div>

    <div class="container" id="chatContainer" style="display: none;">
        <h2>📜 聊天记录</h2>
        <div id="chatLog"></div>
    </div>

    <div id="fileUpload" style="display: none;">
        <input type="file" id="fileInput" hidden>
        <button onclick="document.getElementById('fileInput').click()">📎 上传文件</button>
        <div id="preview"></div>
    </div>

    <form id="sendForm" style="display: none;">
        <input type="text" id="messageInput" placeholder="输入消息内容... 使用@username提及用户" required>
        <button type="submit">发送</button>
    </form>

    <script>
        let currentUser = null;
        let source = null;
        let loading = false;
        let hasMore = true;
        let oldestId = Infinity;

        // 认证相关函数
        function showRegister() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
            clearMessages();
        }

        function showLogin() {
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';
            clearMessages();
        }

        function clearMessages() {
            document.getElementById('loginMessage').innerHTML = '';
            document.getElementById('registerMessage').innerHTML = '';
        }

        function showError(elementId, message) {
            document.getElementById(elementId).innerHTML = `<div class="error-message">${message}</div>`;
        }

        function showSuccess(elementId, message) {
            document.getElementById(elementId).innerHTML = `<div class="success-message">${message}</div>`;
        }

        function validateInput(username, password) {
            if (!username || username.length < 3 || username.length > 20) {
                return '用户名长度必须在3-20字符之间';
            }
            if (!password || password.length < 6) {
                return '密码长度至少6位';
            }
            return null;
        }

        async function login() {
            const username = document.getElementById('loginUser').value.trim();
            const password = document.getElementById('loginPass').value;

            const error = validateInput(username, password);
            if (error) {
                showError('loginMessage', error);
                return;
            }

            try {
                const response = await fetch('https://bm-p8ho.onrender.com/login.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ username, password })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    currentUser = username;
                    localStorage.setItem('currentUser', username);
                    showChatInterface();
                } else {
                    showError('loginMessage', data.error || '登录失败，请检查用户名和密码');
                }
            } catch (error) {
                console.error('登录错误:', error);
                showError('loginMessage', '网络连接失败，请重试');
            }
        }

        async function register() {
            const username = document.getElementById('regUser').value.trim();
            const password = document.getElementById('regPass').value;

            const error = validateInput(username, password);
            if (error) {
                showError('registerMessage', error);
                return;
            }

            try {
                const response = await fetch('https://bm-p8ho.onrender.com/register.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ username, password })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showSuccess('registerMessage', '注册成功！请登录');
                    setTimeout(() => showLogin(), 1500);
                } else {
                    showError('registerMessage', data.error || '注册失败');
                }
            } catch (error) {
                console.error('注册错误:', error);
                showError('registerMessage', '网络连接失败，请重试');
            }
        }

        function showChatInterface() {
            document.getElementById('authBox').style.display = 'none';
            document.getElementById('chatContainer').style.display = 'block';
            document.getElementById('sendForm').style.display = 'flex';
            document.getElementById('fileUpload').style.display = 'block';
            document.getElementById('onlineStatus').style.display = 'block';

            // 初始化聊天功能
            connectSSE();
            loadHistory();
            updateOnlineList();
            setInterval(updateOnlineList, 10000);
            setInterval(() => {
                fetch('https://bm-p8ho.onrender.com/flask/update-activity', { 
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ username: currentUser })
                });
            }, 30000);
        }

        // 文件上传处理
        document.getElementById('fileInput').addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                alert('文件大小不能超过10MB');
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('username', currentUser);

            try {
                const response = await fetch('https://bm-p8ho.onrender.com/flask/upload', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.url) {
                    // 显示预览
                    const preview = document.getElementById('preview');
                    preview.innerHTML = '';
                    
                    if (file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.src = data.url;
                        preview.appendChild(img);
                    } else {
                        preview.innerHTML = `<p>文件上传成功: ${file.name}</p>`;
                    }
                    
                    // 自动发送文件消息
                    sendMessage(`[文件] ${file.name} ${data.url}`);
                    
                    // 清除预览
                    setTimeout(() => preview.innerHTML = '', 3000);
                } else {
                    alert('文件上传失败');
                }
            } catch (err) {
                console.error('上传失败:', err);
                alert('上传失败，请重试');
            }
        });

        function sendMessage(text) {
            document.getElementById('messageInput').value = text;
            document.querySelector('button[type="submit"]').click();
        }

        // 消息发送处理
        document.getElementById('sendForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const text = document.getElementById('messageInput').value.trim();
            if (!text) return;

            try {
                const response = await fetch('https://bm-p8ho.onrender.com/flask/send', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ 
                        text: text,
                        username: currentUser 
                    })
                });

                if (response.ok) {
                    document.getElementById('messageInput').value = '';
                }
            } catch (error) {
                console.error('发送消息失败:', error);
            }
        });

        // SSE连接
        function connectSSE() {
            if (source) {
                source.close();
            }

            source = new EventSource("https://bm-p8ho.onrender.com/flask/sse");

            source.onmessage = function(event) {
                if (event.data.startsWith(":")) return;
                
                try {
                    const data = JSON.parse(event.data);
                    
                    if (data.type === 'mention' && data.mentioned_users.includes(currentUser)) {
                        // 处理提及通知
                        if (Notification.permission === 'granted') {
                            new Notification('聊天室提及', {
                                body: `有人在聊天中提及了你: ${data.content}`,
                                icon: '/favicon.ico'
                            });
                        }
                    } else if (Array.isArray(data)) {
                        // 处理消息列表
                        updateChatLog(data);
                    }
                } catch (err) {
                    console.error("JSON 解析失败:", err);
                }
            };

            source.onerror = function(err) {
                console.error('SSE 连接错误:', err);
                if (source.readyState === EventSource.CLOSED) {
                    setTimeout(connectSSE, 3000);
                }
            };
        }

        function updateChatLog(messages) {
            const log = document.getElementById('chatLog');
            const shouldScrollToBottom = log.scrollTop + log.clientHeight >= log.scrollHeight - 50;
            
            log.innerHTML = messages.map(m => {
                const isMention = m.mentioned_users && m.mentioned_users.includes(currentUser);
                const mentionClass = isMention ? ' mention' : '';
                
                return `<div class="message-item${mentionClass}">
                    <strong>${m.username || '匿名用户'}</strong>
                    <span>${formatMessage(m.content)}</span>
                    <small>${formatTime(m.created_at)}</small>
                </div>`;
            }).join('');
            
            if (shouldScrollToBottom) {
                log.scrollTop = log.scrollHeight;
            }
        }

        function formatMessage(content) {
            // 处理@提及
            content = content.replace(/@(\w+)/g, '<span style="color: #2196F3; font-weight: bold;">@$1</span>');
            
            // 处理文件链接
            content = content.replace(/\[文件\]\s*(.+?)\s*(https?:\/\/[^\s]+)/g, 
                '<a href="$2" target="_blank" style="color: #4CAF50;">📎 $1</a>');
            
            return content;
        }

        function formatTime(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            return date.toLocaleString('zh-CN', {
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // 历史消息加载
        async function loadHistory() {
            if (loading || !hasMore) return;
            
            loading = true;
            try {
                const response = await fetch(`https://bm-p8ho.onrender.com/flask/history?last_id=${oldestId}`);
                const data = await response.json();

                if (data.messages && data.messages.length > 0) {
                    const container = document.getElementById('chatLog');
                    const scrollHeight = container.scrollHeight;
                    const scrollTop = container.scrollTop;

                    data.messages.reverse().forEach(msg => {
                        const isMention = msg.mentioned_users && msg.mentioned_users.includes(currentUser);
                        const mentionClass = isMention ? ' mention' : '';
                        
                        const html = `<div class="message-item${mentionClass}" data-id="${msg.id}">
                            <strong>${msg.username}</strong>
                            <span>${formatMessage(msg.content)}</span>
                            <small>${formatTime(msg.created_at)}</small>
                        </div>`;
                        container.insertAdjacentHTML('afterbegin', html);
                        oldestId = Math.min(oldestId, msg.id);
                    });

                    // 保持滚动位置
                    container.scrollTop = container.scrollHeight - scrollHeight + scrollTop;
                }
                hasMore = data.has_more;
            } catch (error) {
                console.error('加载历史消息失败:', error);
            } finally {
                loading = false;
            }
        }

        // 滚动事件监听
        document.getElementById('chatLog').addEventListener('scroll', function() {
            if (this.scrollTop === 0 && !loading && hasMore) {
                loadHistory();
            }
        });

        // 在线用户列表
        async function updateOnlineList() {
            try {
                const response = await fetch('https://bm-p8ho.onrender.com/flask/active-users');
                const users = await response.json();

                const list = document.getElementById('onlineList');
                list.innerHTML = users.map(u => `<li>${u}</li>`).join('');
                document.getElementById('onlineCount').textContent = users.length;
            } catch (error) {
                console.error('获取在线用户失败:', error);
            }
        }

        // 请求通知权限
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // 检查是否已登录
        window.addEventListener('load', function() {
            const savedUser = localStorage.getItem('currentUser');
            if (savedUser) {
                currentUser = savedUser;
                // 可以选择自动登录或要求重新输入密码
                document.getElementById('loginUser').value = savedUser;
            }
        });

        // 页面卸载时关闭SSE连接
        window.addEventListener('beforeunload', function() {
            if (source) {
                source.close();
            }
        });
    </script>
</body>
</html>