FROM ubuntu:22.04

# 安装基础依赖
RUN apt update && DEBIAN_FRONTEND=noninteractive apt install -y \
    curl \
    bash \
    procps \
    ncurses-bin \
    openssl \
    nginx \
    php-fpm \
    python3 \
    python3-pip \
    && rm -rf /var/lib/apt/lists/*

# 安装Flask
RUN pip3 install flask

# 下载并安装GoTTY
RUN curl -LO https://github.com/yudai/gotty/releases/download/v1.0.1/gotty_linux_amd64.tar.gz \
    && tar zxvf gotty_linux_amd64.tar.gz \
    && mv gotty /usr/local/bin/ \
    && chmod +x /usr/local/bin/gotty \
    && rm gotty_linux_amd64.tar.gz



# 配置PHP测试文件和目录
RUN mkdir -p /var/www/html/php \
    && echo "<?php phpinfo(); ?>" > /var/www/html/php/info.php \
    && chown -R www-data:www-data /var/www/html/php

# 复制Nginx配置文件
COPY nginx.conf /etc/nginx/sites-available/default
# 配置非root用户并生成证书
RUN useradd -m appuser && \
    # 安装sudo
    apt update && apt install -y sudo && \
    # 添加sudo权限
    usermod -aG sudo appuser && \
    echo 'appuser ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers && \
    # 生成证书
    openssl req -x509 -newkey rsa:4096 -nodes -days 365 \
      -subj "/CN=localhost" \
      -keyout /home/appuser/.gotty.key \
      -out /home/appuser/.gotty.crt && \
    chown appuser:appuser /home/appuser/.gotty.*

USER appuser
# 配置Flask应用
COPY flask_app.py /home/appuser/flask_app.py
RUN chown appuser:appuser /home/appuser/flask_app.py

# 复制启动脚本并设置权限
COPY start.sh /start.sh
RUN chmod +x /start.sh



# 暴露端口
EXPOSE 80

# 启动服务
CMD ["/start.sh"]