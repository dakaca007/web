import json
import time
from datetime import datetime
from flask import Flask, jsonify, request, Response
from flask_sqlalchemy import SQLAlchemy
import threading
import requests
from flask_jwt_extended import JWTManager, create_access_token, jwt_required
app = Flask(__name__)
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///messages.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False
db = SQLAlchemy(app)

class Message(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(50), nullable=False)
    content = db.Column(db.String(500), nullable=False)
    timestamp = db.Column(db.DateTime, default=datetime.utcnow)

with app.app_context():
    db.create_all()

# SSE 订阅者管理
subscribers = []
db_lock = threading.Lock()

def keep_alive():
    while True:
        requests.get("https://bm-p8ho.onrender.com/health-check")
        threading.Event().wait(300)


app.config['JWT_SECRET_KEY'] = 'your-secret-key-123'
jwt = JWTManager(app)

@app.route('/login', methods=['POST'])
def login():
    username = request.json.get('username')
    # 简单示例，实际应验证密码
    return jsonify(token=create_access_token(identity=username))
@app.route('/send', methods=['POST'])
@jwt_required()
def send():
    current_user = get_jwt_identity()
    data = request.json
    if not data or 'content' not in data or 'username' not in data:
        return jsonify(ok=0, error="Invalid request"), 400
    
    with db_lock:
        new_message = Message(
            username=data['username'],
            content=data['content']
        )
        db.session.add(new_message)
        db.session.commit()
    
    # 通知所有订阅者
    for sub in subscribers[:]:
        try:
            sub.queue.put(new_message)
        except:
            subscribers.remove(sub)
    
    return jsonify(ok=1)

@app.route('/sse')
def sse():
    def event_stream():
        subscriber_queue = Queue()
        subscribers.append(subscriber_queue)
        
        try:
            # 发送最近20条历史消息
            messages = Message.query.order_by(Message.timestamp.desc()).limit(20).all()[::-1]
            for msg in messages:
                yield format_sse(msg)
                
            while True:
                msg = subscriber_queue.get()
                yield format_sse(msg)
        finally:
            subscribers.remove(subscriber_queue)
    
    return Response(event_stream(), mimetype='text/event-stream')

def format_sse(message):
    data = {
        "id": message.id,
        "username": message.username,
        "content": message.content,
        "time": message.timestamp.strftime("%Y-%m-%d %H:%M:%S")
    }
    return f"data: {json.dumps(data)}\n\n"

@app.route('/messages')
def get_messages():
    limit = request.args.get('limit', default=50, type=int)
    messages = Message.query.order_by(Message.timestamp.desc()).limit(limit).all()[::-1]
    return jsonify([{
        'id': m.id,
        'username': m.username,
        'content': m.content,
        'time': m.timestamp.strftime("%Y-%m-%d %H:%M:%S")
    } for m in messages])

@app.route('/health-check')
def health_check():
    return {"status": "healthy"}, 200

if __name__ == '__main__':
    threading.Thread(target=keep_alive, daemon=True).start()
    