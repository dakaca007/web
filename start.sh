#!/bin/bash

# 启动MySQL服务
service mysql start

# 初始化数据库(如果/docker-entrypoint-initdb.d/中有SQL文件)
for f in /docker-entrypoint-initdb.d/*; do
    case "$f" in
        *.sql)    echo "Processing $f"; mysql < "$f"; echo ;;
        *.sql.gz) echo "Processing $f"; gunzip -c "$f" | mysql; echo ;;
    esac
done

# 启动PHP-FPM
service php8.1-fpm start

# 启动Nginx
nginx -g "daemon off;"