from flask import Flask
app = Flask(__name__)

@app.route('/')
def hello():
    return "Hello from Flask!"

@app.route('/api')
def api():
    return {"status": "success", "message": "Flask API Working"}