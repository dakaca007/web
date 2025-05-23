#!/bin/bash
export JAVA_HOME=/usr/lib/jvm/java-17-openjdk-amd64
# 启动php-fpm服务
service php8.1-fpm start
# 启动Flask应用（使用gunicorn）
cd /var/www/html/flaskapp && gunicorn -b 0.0.0.0:8000 app:app &
# 以appuser用户启动GoTTY
su - appuser -c "gotty --permit-write --port 3000 bash" &

# 前台运行Nginx
nginx -g "daemon off;"