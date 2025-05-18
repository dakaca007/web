# 使用 Ubuntu 作为基础镜像（支持 PHP + Nginx + Supervisord）
FROM ubuntu:22.04
# 设置时区 & 非交互模式
ENV DEBIAN_FRONTEND=noninteractive TZ=Asia/Shanghai
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# 安装依赖
RUN apt update && \
    apt install -y \
    php php-cli php-curl \
    nginx \
    supervisor \
    curl \
    git \
    libcurl4 \
    libssl-dev && \
    rm -rf /var/lib/apt/lists/*

# 安装 Swoole 扩展
RUN pecl install swoole && \
    echo "extension=swoole.so" > /etc/php/8.1/cli/conf.d/20-swoole.ini

# 复制项目文件
WORKDIR /usr/src/app
COPY ./terminal.sh /usr/bin/terminal.sh
RUN chmod +x /usr/bin/terminal.sh
# 配置 Nginx
RUN rm /etc/nginx/sites-enabled/default
COPY ./nginx.conf /etc/nginx/sites-available/default
RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# 配置 Supervisord
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 安装 Composer（可选）
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 安装 PHP 依赖（可选）
RUN composer install --no-dev --optimize-autoloader

# 暴露 80 端口
EXPOSE 80

# 启动服务
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]