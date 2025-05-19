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
	<div id="authBar" style="position: fixed; top: 0; right: 20px; padding: 10px; background: white; z-index: 1000;">
    <span id="userStatus">未登录</span>
    <button onclick="showLogin()">登录</button>
    <button onclick="showRegister()">注册</button>
    <button onclick="logout()" style="display: none;">退出</button>
</div>
    <div class="container">
        <h2>📜 聊天记录</h2>
        <div id="chatLog"></div>
    </div>

    <form id="sendForm">
        <input type="text" id="messageInput" placeholder="输入消息内容..." required>
        <button type="submit">发送</button>
    </form>

    <script>

		// 检查登录状态
function checkAuth() {
    fetch('/flask/check_auth')
        .then(r => r.json())
        .then(data => {
            if (data.logged_in) {
                document.getElementById('userStatus').textContent = `已登录：${data.username}`;
                document.querySelectorAll('button').forEach(b => {
                    b.style.display = b.textContent === '退出' ? 'inline' : 'none';
                });
            }
        });
}

// 显示登录弹窗
function showLogin() {
    const username = prompt('请输入用户名:');
    const password = prompt('请输入密码:');
    fetch('/flask/login', {
        method: 'POST',
        body: new URLSearchParams({ username, password })
    }).then(r => {
        if(r.ok) {
            checkAuth();
            location.reload();
        }
    });
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