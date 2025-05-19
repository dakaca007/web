import json
import time
import requests
from flask import Flask, jsonify, request
import threading
app = Flask(__name__)
def keep_alive():
    while True:
        requests.get("https://bm-p8ho.onrender.com/health-check")
        threading.Event().wait(300)  # 每5分钟执行
messages = []
lock = threading.Lock()

@app.route('/send', methods=['POST'])
def send():
    msg = request.form.get('text')
    with lock:
        messages.append(msg)
    return jsonify(ok=1)

@app.route('/sse')
def sse():
    try:
        def event_stream():
            last_len = 0
            while True:
                with lock:
                    if len(messages) > last_len:
                        yield f"data: {json.dumps(messages)}\n\n"
                        last_len = len(messages)
                time.sleep(0.5)
        return Response(event_stream(), mimetype='text/event-stream')
    except Exception as e:
        return jsonify(error=str(e)), 500
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
