FROM ubuntu:22.04

# 设置非交互式时区配置
ENV DEBIAN_FRONTEND=noninteractive TZ=Asia/Shanghai
RUN echo $TZ > /etc/timezone && \
    apt update && \
    apt install -y tzdata && \
    dpkg-reconfigure --frontend noninteractive tzdata

# 安装 PHP CLI + 开发工具
RUN apt update && \
    apt install -y \
    php php-cli php-curl \
    php-dev php-pear \  
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

# 设置工作目录
WORKDIR /usr/src/app

# 复制项目文件
COPY . .

# 配置 Nginx
RUN rm /etc/nginx/sites-enabled/default
COPY nginx.conf /etc/nginx/sites-available/default
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