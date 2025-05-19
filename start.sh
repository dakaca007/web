#!/bin/bash

# 启动php-fpm服务
service php8.1-fpm start

# 以appuser用户启动GoTTY
su - appuser -c "gotty --permit-write --port 3000 bash" &

# 前台运行Nginx
nginx -g "daemon off;"