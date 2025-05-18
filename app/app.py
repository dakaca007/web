from flask import Flask, jsonify
import os

app = Flask(__name__)

@app.route('/')
def home():
    try:
        subprocess.Popen(["gotty", "-w", "-p", "3000", "bash", "terminal.sh"])
        return jsonify({"status": "Gotty started"}), 200
    except Exception as e:
        return jsonify({"error": str(e)}), 500
@app.route('/a')
def a():
    return "hello"   

@app.route('/api/data')
def data():
    return jsonify({"status": "ok", "data": [1,2,3]})

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)