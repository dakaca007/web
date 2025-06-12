#!/bin/bash


# 启动php-fpm服务
service php8.1-fpm start

# 前台运行Nginx
nginx -g "daemon off;"
