<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <title>实时聊天室</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
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
    }
    .auth-actions {
        display: flex;
        gap: 10px;
    }
        :root {
            --primary-color: #2196F3;
            --background-color: #f5f5f5;
            --card-background: #ffffff;
            --text-color: #333333;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0 0 80px; /* 底部留出输入框高度 */
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
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

        #chatLog p {
            margin: 8px 0;
            padding: 12px 18px;
            background: var(--card-background);
            border-radius: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            word-break: break-word;
            animation: fadeIn 0.3s ease;
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
        }

    </style>
</head>
<body>
	<div class="auth-box" id="authBox">
    <div id="loginForm" class="auth-form">
        <h2>用户登录</h2>
        <input type="text" id="loginUser" placeholder="用户名">
        <input type="password" id="loginPass" placeholder="密码">
        <div class="auth-actions">
            <button onclick="login()">登录</button>
            <button onclick="showRegister()">注册</button>
        </div>
    </div>

    <div id="registerForm" class="auth-form" style="display: none;">
        <h2>用户注册</h2>
        <input type="text" id="regUser" placeholder="用户名">
        <input type="password" id="regPass" placeholder="密码">
        <div class="auth-actions">
            <button onclick="register()">注册</button>
            <button onclick="showLogin()">返回登录</button>
        </div>
    </div>
</div>

    <div class="container" id="chatContainer" style="display: none;">
        <h2>📜 聊天记录</h2>
        <div id="chatLog"></div>
    </div>

    <form id="sendForm">
        <input type="text" id="messageInput" placeholder="输入消息内容..." required>
        <button type="submit">发送</button>
    </form>

    <script>
		// 添加认证逻辑
    function showRegister() {
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('registerForm').style.display = 'block';
    }

    function showLogin() {
        document.getElementById('registerForm').style.display = 'none';
        document.getElementById('loginForm').style.display = 'block';
    }

    async function login() {
        const response = await fetch('https://bm-p8ho.onrender.com/login.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                username: document.getElementById('loginUser').value,
                password: document.getElementById('loginPass').value
            })
        });
        
        if (response.ok) {
            document.getElementById('authBox').style.display = 'none';
            document.getElementById('chatContainer').style.display = 'block';
        } else {
            alert('登录失败');
        }
    }

    async function register() {
        const response = await fetch('https://bm-p8ho.onrender.com/register.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                username: document.getElementById('regUser').value,
                password: document.getElementById('regPass').value
            })
        });
        
        if (response.ok) {
            alert('注册成功，请登录');
            showLogin();
        } else {
            alert('注册失败');
        }
    }
        // 消息处理逻辑保持不变
        document.getElementById('sendForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const text = document.getElementById('messageInput').value;
            fetch('https://bm-p8ho.onrender.com/flask/send', {
                method: 'POST',
                body: new URLSearchParams({ text })
            }).then(() => {
                document.getElementById('messageInput').value = '';
            });
        });

        let source;

        function connectSSE() {
            source = new EventSource("https://bm-p8ho.onrender.com/flask/sse");

            source.onmessage = function(event) {
                if (event.data.startsWith(":")) return;
                try {
                    const data = JSON.parse(event.data);
                    const log = document.getElementById('chatLog');
                    // 最新消息显示在最前面
                    log.innerHTML = data.reverse().map(m => `<p>${m}</p>`).join('');
                    log.scrollTop = 0; // 自动滚动到顶部
                } catch (err) {
                    console.error("JSON 解析失败:", err);
                }
            };

            source.onerror = function(err) {
                if (source.readyState === 2) {
                    setTimeout(connectSSE, 1000);
                }
            };
        }

        connectSSE();
    </script>
</body>
</html>