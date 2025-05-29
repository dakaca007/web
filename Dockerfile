FROM ubuntu:22.04

# 替换APT为阿里云镜像源
RUN sed -i 's/archive.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list && \
    sed -i 's/security.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list

# 安装基础依赖
RUN apt update && DEBIAN_FRONTEND=noninteractive apt install -y \
    curl \
    bash \
    procps \
    ncurses-bin \
    openssl \
    nginx \
    python3 \
    python3-pip \
    vim \
    && rm -rf /var/lib/apt/lists/*

# 配置Nginx目录权限
RUN mkdir -p /var/log/nginx /var/lib/nginx /var/www/html/php \
    && chown -R www-data:www-data /var/log/nginx /var/lib/nginx /var/www/html \
    && chmod 755 /var/log/nginx /var/lib/nginx

# 下载并安装GoTTY（保持原地址）
RUN curl -LO https://github.com/yudai/gotty/releases/download/v1.0.1/gotty_linux_amd64.tar.gz \
    && tar zxvf gotty_linux_amd64.tar.gz \
    && mv gotty /usr/local/bin/ \
    && chmod +x /usr/local/bin/gotty \
    && rm gotty_linux_amd64.tar.gz

# 安装PHP-FPM及相关扩展
RUN apt update && DEBIAN_FRONTEND=noninteractive apt install -y \
    php8.1-fpm \
    php8.1-mysql \
    php8.1-odbc \
    php8.1-pdo \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html/php
RUN mkdir -p /var/www/uploads

# PHP运行环境配置
RUN mkdir -p /run/php && chown www-data:www-data /run/php
RUN echo "<?php phpinfo(); ?>" > /var/www/html/php/info.php \
    && echo "<?php echo 'Hello from PHP test!'; ?>" > /var/www/html/php/test.php \
    && chown -R www-data:www-data /var/www/html/php \
    && chmod 755 /var/www/html/php/*.php
COPY ./myphp /var/www/html/php

# 配置Nginx
COPY nginx.conf /etc/nginx/sites-available/default

# 使用阿里云PyPI镜像安装Python依赖
COPY ./flaskapp/requirements.txt /tmp/requirements.txt
RUN python3 -m pip install --no-cache-dir -r /tmp/requirements.txt \
    -i https://mirrors.aliyun.com/pypi/simple/ \
    --trusted-host mirrors.aliyun.com \
    && rm /tmp/requirements.txt

# 部署Flask应用
COPY ./flaskapp /var/www/html/flaskapp
RUN mkdir -p /var/www/html/flaskapp/static/uploads \
    && chown -R www-data:www-data /var/www/html/flaskapp/static \
    && chmod 755 /var/www/html/flaskapp

# 配置非root用户
RUN useradd -m appuser \
    && apt update && apt install -y sudo \
    && usermod -aG sudo appuser \
    && echo 'appuser ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers \
    && openssl req -x509 -newkey rsa:4096 -nodes -days 365 \
      -subj "/CN=localhost" \
      -keyout /home/appuser/.gotty.key \
      -out /home/appuser/.gotty.crt \
    && chown appuser:appuser /home/appuser/.gotty.*

# 配置启动脚本
COPY start.sh /start.sh
RUN chown appuser:appuser /start.sh && chmod +x /start.sh

# 暴露端口
EXPOSE 80

# 启动服务
CMD ["/start.sh"]
