# 基于 Ubuntu 22.04
FROM ubuntu:22.04

# 设置时区 & 非交互模式
ENV DEBIAN_FRONTEND=noninteractive TZ=Asia/Shanghai
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# 安装系统依赖
RUN apt update && apt install -y \
    python3 python3-pip python3-venv \
    mysql-client \
    nginx supervisor wget unzip \
    npm git golang gettext \
    && rm -rf /var/lib/apt/lists/*

# 安装最新版 Go
RUN wget https://go.dev/dl/go1.21.1.linux-amd64.tar.gz \
    && tar -C /usr/local -xzf go1.21.1.linux-amd64.tar.gz \
    && rm go1.21.1.linux-amd64.tar.gz
ENV PATH="$PATH:/usr/local/go/bin"

# 安装 Gotty（Web 终端）
RUN go install github.com/sorenisanerd/gotty@latest
ENV PATH="$PATH:/root/go/bin"

# 复制代码
WORKDIR /app
COPY ./terminal.sh /app
COPY ./terminal.sh /usr/bin/
RUN chmod +x /usr/bin/terminal.sh
COPY ./nginx.conf /etc/nginx/templates/
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 暴露端口 (Render 使用环境变量 PORT)
ENV PORT=80
EXPOSE $PORT

# 启动服务
CMD ["sh", "-c", "envsubst '$PORT' < /etc/nginx/templates/nginx.conf > /etc/nginx/nginx.conf && /usr/bin/supervisord -n"]