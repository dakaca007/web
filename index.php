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

        const source = new EventSource("https://bm-p8ho.onrender.com/flask/sse");

source.onerror = function(err) {
    console.error("SSE 错误:", err);
};

source.onopen = function() {
    console.log("SSE 连接已建立");
};

source.onmessage = function(event) {
    try {
        const data = JSON.parse(event.data);
        const log = document.getElementById('chatLog');
        log.innerHTML = data.map(m => `<p>${m}</p>`).join('');
        log.scrollTop = log.scrollHeight;
    } catch (err) {
        console.error("JSON 解析失败:", err);
    }
};
    </script>
</body>
</html>