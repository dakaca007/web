FROM ubuntu:22.04

# 设置非交互式时区配置
ENV DEBIAN_FRONTEND=noninteractive TZ=Asia/Shanghai
ENV PORT=3000
RUN echo $TZ > /etc/timezone && \
    apt update && \
    apt install -y tzdata && \
    dpkg-reconfigure --frontend noninteractive tzdata

# 安装核心依赖（包含编译工具）
RUN apt update && \
    apt install -y \
    php php-cli php-curl \
    php-dev php-pear \
    php-dom php-mbstring php-xml php-zip php-json \
    php-bcmath php-gd php-intl php-soap php-opcache \
    php-tokenizer php-xmlwriter php-ctype php-iconv php-simplexml php-posix \
    nginx \
    supervisor \
    curl git \
    libcurl4 libssl-dev libbrotli-dev \
    zlib1g-dev libcurl4-openssl-dev \
    g++ make autoconf \  
    unzip \
    zip && \
    rm -rf /var/lib/apt/lists/*

# 安装 Swoole 扩展（确认 PHP 版本路径）
RUN pecl install swoole && \
    echo "extension=swoole.so" > /etc/php/8.1/cli/conf.d/20-swoole.ini

# 内存限制配置
RUN echo "memory_limit = 2G" > /etc/php/8.1/cli/conf.d/99-custom.ini

# 设置工作目录
WORKDIR /usr/src/app

# 复制项目文件
COPY . .

# 配置 Nginx（修复 WebSocket 代理）
RUN rm -f /etc/nginx/sites-enabled/default
COPY nginx.conf /etc/nginx/sites-available/default
RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# 配置 Supervisord
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 安装 Composer（可选）
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

# 安装 PHP 依赖（仅在 composer.json 存在时执行）
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader; fi

# 暴露端口
EXPOSE 80

# 启动服务
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]