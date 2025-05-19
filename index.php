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
<div id="login-panel">
    <h3>请输入用户名</h3>
    <input type="text" id="usernameInput" placeholder="用户名">
    <button onclick="login()">登录</button>
</div>
<div id="chat-app" style="display:none;">
    <div id="contacts"></div>
    <div id="chat-window"></div>
    <form id="sendForm">
        <input type="text" id="messageInput" placeholder="输入消息...">
        <select id="recipientSelect">
            <option value="">群聊</option>
            <!-- 动态填充联系人 -->
        </select>
        <button type="submit">发送</button>
    </form>
</div>
<script>
let currentUser = null;

function login() {
    const username = document.getElementById("usernameInput").value;
    fetch('/login', {
        method: 'POST',
        body: new URLSearchParams({ username })
    }).then(res => res.json())
      .then(data => {
          currentUser = data.user_id;
          document.getElementById("login-panel").style.display = "none";
          document.getElementById("chat-app").style.display = "block";
          connectSSE();
      });
}
</script>
    <script>
        document.getElementById('sendForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const text = document.getElementById('messageInput').value;
    const to = document.getElementById('recipientSelect').value;

    fetch('/send', {
        method: 'POST',
        body: new URLSearchParams({ text, to })
    }).then(() => {
        document.getElementById('messageInput').value = '';
    });
});

      function connectSSE() {
    const source = new EventSource("/sse");

    source.onmessage = function(event) {
        try {
            const data = JSON.parse(event.data);
            const chatWindow = document.getElementById('chat-window');
            
            if (data.type === 'group') {
                chatWindow.innerHTML += `<p><b>${data.name}:</b> ${data.msg.text}</p>`;
            } else if (data.type === 'private' && (data.from === selectedContact || data.from === currentUser)) {
                chatWindow.innerHTML += `<p><i>私聊 | ${data.name}:</i> ${data.msg.text}</p>`;
            }
        } catch (e) {
            console.error("解析消息失败:", e);
        }
    };

    source.onerror = function(err) {
        setTimeout(connectSSE, 1000);
    };
}


setInterval(() => {
    fetch('/users').then(res => res.json()).then(data => {
        const select = document.getElementById('recipientSelect');
        select.innerHTML = '<option value="">群聊</option>';
        Object.entries(data.users).forEach(([uid, name]) => {
            if (uid !== currentUser) {
                select.innerHTML += `<option value="${uid}">${name}</option>`;
            }
        });
    });
}, 5000);
    </script>
</body>
</html>