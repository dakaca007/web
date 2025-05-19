#!/bin/bash

# 启动php-fpm服务
service php8.1-fpm start

# 以appuser用户启动Flask应用
su - appuser -c "python3 /home/appuser/flask_app.py" &

# 以appuser用户启动GoTTY
su - appuser -c "gotty --permit-write --port 3000 bash" &

# 前台运行Nginx
nginx -g "daemon off;"