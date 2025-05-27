<!DOCTYPE html>
<html>
<head>
    <title>Flask WebSocket</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.0.1/socket.io.js"></script>
</head>
<body>
    <div id="messages"></div>
    <input type="text" id="messageInput">
    <button onclick="sendMessage()">Send</button>

    <script>
        const socket = io.connect('https://' + document.domain, {
    path: '/flask/socket.io'  // 根据服务端路由调整
});
        // 监听服务端消息
        socket.on('server_response', function(data) {
            const div = document.createElement('div');
            div.textContent = data.data;
            document.getElementById('messages').appendChild(div);
        });

        // 发送消息到服务端
        function sendMessage() {
            const input = document.getElementById('messageInput');
            socket.emit('client_message', { msg: input.value });
            input.value = '';
        }
    </script>
</body>
</html>