import json
import time
import requests
import pymysql.cursors
from flask import Flask, jsonify, request, Response
import threading
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
@app.route('/send', methods=['POST'])
def send():
    db = get_db()
    try:
        with db.cursor() as cursor:
            content = request.form.get('text')
            # 实际应用中需要验证用户身份
            cursor.execute("INSERT INTO messages (user_id, content) VALUES (1, %s)", (content,))
            db.commit()
        return jsonify(ok=1)
    finally:
        db.close()

@app.route('/sse')
def sse():
    def event_stream():
        last_id = 0
        while True:
            db = get_db()
            try:
                with db.cursor() as cursor:
                    cursor.execute("SELECT * FROM messages WHERE id > %s ORDER BY id DESC LIMIT 10", (last_id,))
                    messages = cursor.fetchall()
                    if messages:
                        last_id = messages[0]['id']
                        yield f"data: {json.dumps([m['content'] for m in messages])}\n\n"
            finally:
                db.close()
            time.sleep(0.5)
    return Response(event_stream(), mimetype='text/event-stream')
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