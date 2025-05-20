import json
import time
import requests
import pymysql.cursors
from flask import Flask, jsonify, request, Response
import threading
mport os
import uuid
from werkzeug.utils import secure_filename
app = Flask(__name__)
def keep_alive():
    while True:
        requests.get("https://bm-p8ho.onrender.com/health-check")
        threading.Event().wait(300)  # 每5分钟执行
messages = []
lock = threading.Lock()
# MySQL配置
db_conf = {
    'host': 'mysql.sqlpub.com',
    'user': 'sujiangxi',
    'password': 'U4JcgUOkcHMI1suU',
    'db': 'mysql_app',
    'charset': 'utf8mb4',
    'cursorclass': pymysql.cursors.DictCursor
}

def get_db():
    return pymysql.connect(**db_conf)
UPLOAD_FOLDER = '/var/www/uploads'
ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'gif', 'pdf', 'docx'}

app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER

def allowed_file(filename):
    return '.' in filename and \
           filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

@app.route('/upload', methods=['POST'])
def upload_file():
    # 实际应验证用户身份
    user_id = 1  
    
    if 'file' not in request.files:
        return jsonify(error="No file part"), 400
    
    file = request.files['file']
    if file.filename == '':
        return jsonify(error="No selected file"), 400
    
    if file and allowed_file(file.filename):
        filename = secure_filename(file.filename)
        unique_name = f"{uuid.uuid4()}_{filename}"
        save_path = os.path.join(app.config['UPLOAD_FOLDER'], unique_name)
        file.save(save_path)
        
        # 记录到数据库
        db = get_db()
        try:
            with db.cursor() as cursor:
                cursor.execute("""
                    INSERT INTO uploaded_files 
                    (user_id, filename, filepath, filetype)
                    VALUES (%s, %s, %s, %s)
                """, (user_id, filename, unique_name, file.content_type))
                db.commit()
            return jsonify(url=f"/download/{unique_name}")
        finally:
            db.close()
    
    return jsonify(error="File type not allowed"), 400

@app.route('/download/<filename>')
def download_file(filename):
    return send_from_directory(app.config['UPLOAD_FOLDER'], filename)
@app.route('/send', methods=['POST'])
def send():
    db = get_db()
    try:
        with db.cursor() as cursor:
            content = request.form.get('text')
            # 实际应用中需要验证用户身份
            #cursor.execute("INSERT INTO messages (user_id, content) VALUES (1, %s)", (content,))
            mentioned = extract_mentions(content)  # 新增解析函数
    
            cursor.execute("""
                INSERT INTO messages 
                (user_id, content, mentioned_users) 
                VALUES (1, %s, %s)
            """, (content, json.dumps(mentioned)))
            db.commit()
        return jsonify(ok=1)
    finally:
        db.close()
def extract_mentions(text):
    import re
    matches = re.findall(r'@(\w+)', text)
    return matches
@app.route('/sse')
def sse():
    def event_stream():
        last_id = 0
        while True:
            try:
                db = get_db()  # 获取数据库连接（建议使用连接池优化）
                with db.cursor() as cursor:
                    # 查询比 last_id 新的消息，按 ID 升序确保顺序正确
                    cursor.execute(
                        "SELECT id, content FROM messages WHERE id > %s ORDER BY id ASC LIMIT 10",
                        (last_id,)
                    )
                    messages = cursor.fetchall()
                    
                    # 在event_stream函数内添加：
                    if messages:
                        for msg in messages:
                            if msg['mentioned_users']:
                                notification = {
                                    'type': 'mention',
                                    'message_id': msg['id'],
                                    'content': msg['content']
                                }
                                yield f"data: {json.dumps(notification)}\n\n"
            except Exception as e:
                print(f"SSE Error: {e}")
                time.sleep(1)  # 出错时稍作等待
                continue
            finally:
                if 'db' in locals() and db:  # 确保连接被关闭
                    db.close()
            
            time.sleep(0.05)  # 调整轮询间隔（平衡响应速度与负载）

    return Response(event_stream(), mimetype='text/event-stream')
@app.route('/history')
def get_history():
    last_id = request.args.get('last_id', 0, type=int)
    limit = 20
    
    db = get_db()
    try:
        with db.cursor() as cursor:
            cursor.execute("""
                SELECT m.id, m.content, u.username, m.created_at 
                FROM messages m
                JOIN users u ON m.user_id = u.id
                WHERE m.id < %s
                ORDER BY m.id DESC 
                LIMIT %s
            """, (last_id, limit))
            messages = cursor.fetchall()
            
            # 将时间转换为前端友好格式
            for msg in messages:
                msg['created_at'] = msg['created_at'].isoformat()
            
            return jsonify({
                'messages': messages,
                'has_more': len(messages) == limit
            })
    finally:
        db.close()
@app.route('/health-check')
def health_check():
    return {"status": "healthy"}, 200
@app.route('/')
def hello():
    return "Hello from Flask!"

@app.route('/api')
def api():
    return {"status": "success", "message": "Flask API Working"}
# 在现有路由后添加
@app.route('/update-activity', methods=['POST'])
def update_activity():
    # 实际应用中需要验证用户身份
    user_id = 1  # 示例用户ID，应根据实际情况获取
    db = get_db()
    try:
        with db.cursor() as cursor:
            cursor.execute("""
                INSERT INTO user_activity (user_id, last_active)
                VALUES (%s, NOW())
                ON DUPLICATE KEY UPDATE last_active = NOW()
            """, (user_id,))
            db.commit()
        return jsonify(ok=1)
    finally:
        db.close()

@app.route('/active-users')
def get_active_users():
    db = get_db()
    try:
        with db.cursor() as cursor:
            cursor.execute("""
                SELECT u.username 
                FROM user_activity ua
                JOIN users u ON ua.user_id = u.id
                WHERE ua.last_active > NOW() - INTERVAL 5 MINUTE
            """)
            users = [row['username'] for row in cursor.fetchall()]
            return jsonify(users)
    finally:
        db.close()
if __name__ == '__main__':
    threading.Thread(target=keep_alive, daemon=True).start()