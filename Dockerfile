FROM ubuntu:22.04

# 安装基础依赖
RUN apt update && DEBIAN_FRONTEND=noninteractive apt install -y \
    curl \
    bash \
    procps \
    ncurses-bin \
    openssl \
    nginx \
    php8.1-fpm \
    python3 \
    python3-pip \
    sudo \
    && rm -rf /var/lib/apt/lists/*

# 配置Nginx目录权限
RUN mkdir -p /var/log/nginx /var/lib/nginx /var/www/html/php \
    && chown -R www-data:www-data /var/log/nginx /var/lib/nginx /var/www/html \
    && chmod 755 /var/log/nginx /var/lib/nginx

# 下载并安装GoTTY
RUN curl -LO https://github.com/yudai/gotty/releases/download/v1.0.1/gotty_linux_amd64.tar.gz \
    && tar zxvf gotty_linux_amd64.tar.gz \
    && mv gotty /usr/local/bin/ \
    && chmod +x /usr/local/bin/gotty \
    && rm gotty_linux_amd64.tar.gz

WORKDIR /var/www/html/php
# 创建PHP-FPM运行时目录
RUN mkdir -p /run/php && chown www-data:www-data /run/php
# 创建PHP测试文件
RUN echo "<?php phpinfo(); ?>" > /var/www/html/php/info.php \
    && echo "<?php echo 'Hello from PHP test!'; ?>" > /var/www/html/php/test.php \
    && chown -R www-data:www-data /var/www/html/php \
    && chmod 755 /var/www/html/php/*.php
COPY index.php /var/www/html/php
COPY /static /var/www/html/php/static
COPY /myapp /var/www/html/php/myapp

# 复制Nginx配置
COPY nginx.conf /etc/nginx/sites-available/default

# 安装Python依赖
COPY ./flaskapp/requirements.txt /tmp/requirements.txt
RUN python3 -m pip install --no-cache-dir -r /tmp/requirements.txt \
    && rm /tmp/requirements.txt

# 添加Flask应用
COPY ./flaskapp /var/www/html/flaskapp
RUN chown -R www-data:www-data /var/www/html/flaskapp \
    && chmod 755 /var/www/html/flaskapp

# 配置非root用户和证书
RUN useradd -m appuser \
    && usermod -aG sudo appuser \
    && echo 'appuser ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers \
    && openssl req -x509 -newkey rsa:4096 -nodes -days 365 \
      -subj "/CN=localhost" \
      -keyout /home/appuser/.gotty.key \
      -out /home/appuser/.gotty.crt \
    && chown appuser:appuser /home/appuser/.gotty.*

# 复制启动脚本
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]