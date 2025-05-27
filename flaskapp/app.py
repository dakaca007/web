from flask import Flask, render_template
from flask_socketio import SocketIO, send, emit

app = Flask(__name__)
app.config['SECRET_KEY'] = 'your_secret_key_here'
socketio = SocketIO(app, cors_allowed_origins="*")

# 路由提供 HTML 页面
@app.route('/')
def index():
    return render_template('index.html')

# 监听客户端连接事件
@socketio.on('connect')
def handle_connect():
    print('Client connected:', request.sid)
    emit('server_response', {'data': 'Connected to server'})

# 监听客户端消息
@socketio.on('client_message')
def handle_message(data):
    print('Received message:', data)
    # 发送给当前客户端
    emit('server_response', {'data': 'Message received: ' + data['msg']})
    # 广播给所有客户端
    send({'data': 'Broadcast: ' + data['msg']}, broadcast=True)

# 监听客户端断开事件
@socketio.on('disconnect')
def handle_disconnect():
    print('Client disconnected:', request.sid)

if __name__ == '__main__':
    socketio.run(app, host='0.0.0.0', port=8000, debug=True)