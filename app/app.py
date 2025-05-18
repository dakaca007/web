from flask import Flask, jsonify
import os

app = Flask(__name__)

@app.route('/')
def home():
    return "Welcome to Flask App!"

@app.route('/api/data')
def data():
    return jsonify({"status": "ok", "data": [1,2,3]})

if __name__ == '__main__':
     
    app.run(debug=True, host='0.0.0.0', port=5000)