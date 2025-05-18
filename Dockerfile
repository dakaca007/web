# 基于 Ubuntu 22.04
FROM ubuntu:22.04

# 设置时区 & 非交互模式
ENV DEBIAN_FRONTEND=noninteractive TZ=Asia/Shanghai
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# 安装系统依赖
RUN apt update && apt install -y \
    python3 python3-pip python3-venv \
    mysql-server mysql-client \
    nginx supervisor wget unzip \
    npm git golang \
    && rm -rf /var/lib/apt/lists/*

# 配置 MySQL
RUN mkdir -p /var/run/mysqld && chown -R mysql:mysql /var/run/mysqld
COPY mysql/init.sql /docker-entrypoint-initdb.d/
RUN chmod +r /docker-entrypoint-initdb.d/init.sql 
ENV MYSQL_ROOT_PASSWORD=render123
ENV MYSQL_DATABASE=myapp
# 在安装 MySQL 后添加以下步骤
RUN mysqld --initialize-insecure --user=mysql && \
    service mysql start && \
    mysql -uroot -p$MYSQL_ROOT_PASSWORD -e "CREATE DATABASE IF NOT EXISTS $MYSQL_DATABASE;" && \
    mysql -uroot -p$MYSQL_ROOT_PASSWORD $MYSQL_DATABASE < /docker-entrypoint-initdb.d/init.sql && \
    service mysql stop
# 安装 Adminer（数据库管理）
RUN mkdir -p /var/www/adminer && \
    wget -O /var/www/adminer/index.php https://github.com/vrana/adminer/releases/download/v4.8.1/adminer-4.8.1.php

# 安装 Gotty（Web 终端）
RUN go install github.com/sorenisanerd/gotty@latest
ENV PATH="$PATH:/root/go/bin"

# 配置 Python 环境
WORKDIR /app
COPY ./app/requirements.txt .
RUN pip3 install --no-cache-dir -r requirements.txt

# 复制代码
COPY ./app /app
COPY ./nginx.conf /etc/nginx/nginx.conf
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 暴露端口 (Render 使用环境变量 PORT)
EXPOSE 80

# 启动服务
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]