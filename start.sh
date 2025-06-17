#!/bin/bash

# 启动PHP-FPM
service php8.1-fpm start

# 启动Nginx
nginx -g "daemon off;"