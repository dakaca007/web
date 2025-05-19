import json
import time
import requests
from flask import Flask, jsonify, request, Response,session
import threading
import uuid
app = Flask(__name__)
# 用户状态管理（内存中）
online_users = {}  # 格式: {user_id: {"name": "...", "last_active": ...}}
def keep_alive():
    while True:
        requests.get("https://bm-p8ho.onrender.com/health-check")
        threading.Event().wait(300)  # 每5分钟执行
# 消息格式示例
messages = {
    "group_chat": [],  # 群聊消息
    "private": {}      # 私聊消息: {"user1_user2": [...]}
}
lock = threading.Lock()
def get_private_key(user1, user2):
    return "_".join(sorted([user1, user2]))
@app.route('/send', methods=['POST'])
def send():
    user_id = session.get('user_id')
    if not user_id or user_id not in online_users:
        return jsonify({"status": "error", "message": "Not logged in"}), 401

    text = request.form.get('text')
    to = request.form.get('to')  # 接收者ID（空为群聊）

    with lock:
        if not to:  # 群聊
            messages["group_chat"].append({
                "sender": user_id,
                "text": text,
                "timestamp": time.time()
            })
        else:  # 私聊
            private_key = get_private_key(user_id, to)
            if private_key not in messages["private"]:
                messages["private"][private_key] = []
            messages["private"][private_key].append({
                "sender": user_id,
                "text": text,
                "timestamp": time.time()
            })
    return jsonify({"status": "ok"})

@app.route('/sse')
def sse():
    user_id = session.get('user_id')
    if not user_id or user_id not in online_users:
        return Response("Unauthorized", status=401)

    def event_stream():
        last_group_len = 0
        last_private = {}  # 记录每个私聊会话的最后长度
        
        while True:
            with lock:
                # 群聊消息
                group_msgs = messages["group_chat"][last_group_len:]
                for msg in group_msgs:
                    user_info = online_users.get(msg["sender"], {})
                    yield f"data: {json.dumps({'type': 'group', 'msg': msg, 'name': user_info.get('name', 'Unknown')})}\n\n"
                last_group_len = len(messages["group_chat"])

                # 私聊消息
                for key in list(messages["private"].keys()):
                    if user_id in key.split('_'):
                        msgs = messages["private"][key][last_private.get(key, 0):]
                        for msg in msgs:
                            other_user = key.replace(user_id, "").replace("_", "")
                            user_info = online_users.get(other_user, {})
                            yield f"data: {json.dumps({'type': 'private', 'msg': msg, 'name': user_info.get('name', 'Unknown'), 'from': other_user})}\n\n"
                        last_private[key] = len(messages["private"][key])
            
            time.sleep(0.1)
    
    return Response(event_stream(), mimetype='text/event-stream')
@app.route('/users')
def get_users():
    return jsonify({
        "users": {uid: info["name"] for uid, info in online_users.items()}
    })
@app.route('/login', methods=['POST'])
def login():
    username = request.form.get('username')
    if not username:
        return jsonify({"status": "error", "message": "Username required"}), 400
    
    user_id = str(uuid.uuid4())  # 生成唯一用户ID
    online_users[user_id] = {
        "name": username,
        "last_active": time.time()
    }
    
    session['user_id'] = user_id  # 使用会话保持用户状态
    return jsonify({"user_id": user_id, "username": username})
@app.route('/health-check')
def health_check():
    return {"status": "healthy"}, 200
@app.route('/')
def hello():
    return "Hello from Flask!"

@app.route('/api')
def api():
    return {"status": "success", "message": "Flask API Working"}

if __name__ == '__main__':
    threading.Thread(target=keep_alive, daemon=True).start()
