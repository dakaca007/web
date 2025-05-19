import json
import time
import requests
from flask import Flask, jsonify, request, Response, session, redirect, url_for
from werkzeug.security import generate_password_hash, check_password_hash
import threading
from database import SessionLocal, User, Message

app = Flask(__name__)
app.config.update(
    SESSION_COOKIE_SECURE=True,
    SESSION_COOKIE_HTTPONLY=True,
    SESSION_COOKIE_SAMESITE='Lax',
    PERMANENT_SESSION_LIFETIME=86400  # 1天
)
app.secret_key = os.environ.get('SECRET_KEY') or 'your-secret-key-here'

# 初始化数据库连接
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

# 健康检查保持活跃
def keep_alive():
    while True:
        requests.get("https://bm-p8ho.onrender.com/health-check")
        threading.Event().wait(300)

# 用户认证装饰器
def login_required(f):
    def wrapper(*args, **kwargs):
        if 'user_id' not in session:
            return jsonify({"error": "需要登录"}), 401
        return f(*args, **kwargs)
    return wrapper

@app.route('/send', methods=['POST'])
@login_required
def send():
    content = request.form.get('text')
    db = next(get_db())
    new_message = Message(
        content=content,
        user_id=session['user_id'],
        created_at=datetime.now()
    )
    db.add(new_message)
    db.commit()
    return jsonify(ok=1)

@app.route('/sse')
def sse():
    def event_stream():
        last_id = 0
        while True:
            db = next(get_db())
            messages = db.query(Message).filter(Message.id > last_id).order_by(Message.id.desc()).limit(20).all()
            if messages:
                last_id = messages[0].id
                yield f"data: {json.dumps([{'content': m.content, 'user': m.user_id} for m in reversed(messages)])}\n\n"
            time.sleep(1)
    return Response(event_stream(), mimetype='text/event-stream')

@app.route('/register', methods=['POST'])
def register():
    db = next(get_db())
    username = request.form.get('username')
    password = request.form.get('password')
    
    existing_user = db.query(User).filter_by(username=username).first()
    if existing_user:
        return jsonify({"error": "用户名已存在"}), 400
    
    new_user = User(
        username=username,
        password_hash=generate_password_hash(password),
        created_at=datetime.now()
    )
    db.add(new_user)
    db.commit()
    session['user_id'] = new_user.id
    return jsonify(ok=1)

@app.route('/login', methods=['POST'])
def login():
    db = next(get_db())
    username = request.form.get('username')
    password = request.form.get('password')
    
    user = db.query(User).filter_by(username=username).first()
    if not user or not check_password_hash(user.password_hash, password):
        return jsonify({"error": "无效的用户名或密码"}), 401
    
    session['user_id'] = user.id
    return jsonify(ok=1)

@app.route('/logout')
def logout():
    session.pop('user_id', None)
    return redirect(url_for('index'))

# 原有健康检查和路由保持不变...

if __name__ == '__main__':
    threading.Thread(target=keep_alive, daemon=True).start()
    