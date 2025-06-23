FROM ubuntu:22.04

# 使用阿里云镜像源
RUN sed -i 's/archive.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list && \
    sed -i 's/security.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list

# 安装基础依赖
RUN apt update && DEBIAN_FRONTEND=noninteractive apt install -y \
    # 系统工具
    bash curl wget git vim build-essential software-properties-common \
    gcc g++ \
    python3 python3-pip nodejs npm  \
    nginx \
    && rm -rf /var/lib/apt/lists/*

# 设置工作目录
RUN mkdir -p /var/www/html/{go,flask,node,php} /var/log/supervisor
WORKDIR /var/www/html

# 安装Supervisor管理进程
RUN pip3 install supervisor
COPY supervisord.conf /etc/supervisord.conf

# 安装Flask应用依赖
RUN pip3 install flask gunicorn


# 配置Nginx目录权限
RUN mkdir -p /var/log/nginx /var/lib/nginx /var/www/html/php \
    && chown -R www-data:www-data /var/log/nginx /var/lib/nginx /var/www/html \
    && chmod 755 /var/log/nginx /var/lib/nginx
# 配置Nginx
COPY nginx.conf /etc/nginx/sites-available/default



# 2. Flask应用配置
COPY flask_app /var/www/html/flask
RUN pip3 install -r /var/www/html/flask/requirements.txt


# 3. Node.js应用配置
COPY node_app /var/www/html/node
RUN cd /var/www/html/node && npm install





# 暴露端口
EXPOSE 80
# 启动服务
CMD ["/usr/local/bin/supervisord", "-c", "/etc/supervisord.conf"]
