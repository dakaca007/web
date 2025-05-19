<!DOCTYPE html>
<html>
<head>
    <title>实时聊天</title>
    <meta charset="utf-8">
</head>
<body>
    <h2>发送消息</h2>
    <form id="sendForm">
        <input type="text" id="messageInput" placeholder="输入消息..." required>
        <button type="submit">发送</button>
    </form>

    <h2>聊天记录</h2>
    <div id="chatLog"></div>

    <script>
        // 处理消息发送
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
        // 忽略特殊控制信号（如 ": connection closed"）
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
        console.error("SSE错误:", {
            readyState: source.readyState,
            url: source.url,
            error: err
        });

        // 如果是正常关闭（readyState === 2），不重连
        if (source.readyState === 2) {
            console.log("服务器主动关闭连接，将在 1 秒后重新连接");
            setTimeout(connectSSE, 1000);  // 延迟重连
        }
    };
}

// 初始连接
connectSSE();
    </script>
</body>
</html>