FROM ubuntu:22.04

# 设置时区和环境变量
ENV DEBIAN_FRONTEND=noninteractive TZ=Asia/Shanghai \
    PATH="/usr/local/go/bin:/root/go/bin:$PATH" \
    GOTTY_PORT=3000

# 安装依赖
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone \
    && apt update && apt install -y \
       python3 python3-pip python3-venv mysql-client \
       nginx supervisor wget unzip npm git golang gettext \
    && rm -rf /var/lib/apt/lists/* \

    # 安装 Go
    && wget https://go.dev/dl/go1.21.1.linux-amd64.tar.gz \
    && tar -C /usr/local -xzf go1.21.1.linux-amd64.tar.gz \
    && rm go1.21.1.linux-amd64.tar.gz \
    && mkdir -p /root/go/bin \

    # 安装 gotty
    && go install github.com/sorenisanerd/gotty@latest

# 工作目录
WORKDIR /app

# 拷贝配置文件
COPY ./nginx.conf /etc/nginx/templates/nginx.conf.tmpl
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./terminal.sh /usr/bin/terminal.sh
RUN chmod +x /usr/bin/terminal.sh

# 开放端口
EXPOSE 80 ${GOTTY_PORT}

# 启动命令
CMD envsubst '$$GOTTY_PORT' < /etc/nginx/templates/nginx.conf.tmpl > /etc/nginx/nginx.conf && \
    supervisord -n