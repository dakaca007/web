<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <title>实时聊天室</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            padding: 20px;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        h2 {
            color: var(--primary-color);
            margin-top: 0;
            border-bottom: 2px solid #eee;
            padding-bottom: 0.5rem;
        }

        #sendForm {
            display: flex;
            gap: 10px;
            margin-bottom: 2rem;
        }

        #messageInput {
            flex: 1;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        #messageInput:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        button[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #1976D2;
        }

        #chatLog {
            height: 400px;
            overflow-y: auto;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #eee;
        }

        #chatLog p {
            margin: 0.5rem 0;
            padding: 10px 16px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 600px) {
            .container {
                padding: 1rem;
                margin: 10px;
            }

            #sendForm {
                flex-direction: column;
            }

            button[type="submit"] {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📨 发送消息</h2>
        <form id="sendForm">
            <input type="text" id="messageInput" placeholder="输入消息内容..." required>
            <button type="submit">发送</button>
        </form>

        <h2>📜 聊天记录</h2>
        <div id="chatLog"></div>
    </div>

    <script>
        // 原有 JavaScript 代码保持不变
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
                    log.innerHTML = data.map(m => `<p>${m}</p>`).join('');
                    log.scrollTop = log.scrollHeight;
                } catch (err) {
                    console.error("JSON 解析失败:", err);
                }
            };

            source.onerror = function(err) {
                if (source.readyState === 2) {
                    console.log("连接断开，正在尝试重连...");
                    setTimeout(connectSSE, 1000);
                }
            };
        }

        connectSSE();
    </script>
</body>
</html>