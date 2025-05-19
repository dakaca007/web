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
            fetch('/flask/send', {
                method: 'POST',
                body: new URLSearchParams({ text })
            }).then(() => {
                document.getElementById('messageInput').value = '';
            });
        });

        // 使用 EventSource 监听 SSE
        const source = new EventSource("/flask/sse");
        source.onmessage = function(event) {
            const data = JSON.parse(event.data);
            const log = document.getElementById('chatLog');
            log.innerHTML = data.map(m => `<p>${m}</p>`).join('');
            log.scrollTop = log.scrollHeight;
        };
    </script>
</body>
</html>