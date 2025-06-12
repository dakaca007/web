FROM ubuntu:22.04

# 替换APT为阿里云镜像源
RUN sed -i 's/archive.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list && \
    sed -i 's/security.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list

# 安装基础依赖
RUN apt update && DEBIAN_FRONTEND=noninteractive apt install -y \
    bash \
    nginx \
    && rm -rf /var/lib/apt/lists/*

# 配置Nginx目录权限
RUN mkdir -p /var/log/nginx /var/lib/nginx /var/www/html/php \
    && chown -R www-data:www-data /var/log/nginx /var/lib/nginx /var/www/html \
    && chmod 755 /var/log/nginx /var/lib/nginx

# 安装PHP-FPM及相关扩展
RUN apt update && DEBIAN_FRONTEND=noninteractive apt install -y \
    php8.1-fpm \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html/php

# PHP运行环境配置
RUN mkdir -p /run/php && chown www-data:www-data /run/php
RUN echo "<?php phpinfo(); ?>" > /var/www/html/php/info.php \
    && echo "<?php echo 'Hello from PHP test!'; ?>" > /var/www/html/php/test.php \
    && chown -R www-data:www-data /var/www/html/php \
    && chmod 755 /var/www/html/php/*.php
COPY ./myphp /var/www/html/php

# 配置Nginx
COPY nginx.conf /etc/nginx/sites-available/default


# 配置启动脚本
COPY start.sh /start.sh


# 暴露端口
EXPOSE 80

# 启动服务
CMD ["/start.sh"]