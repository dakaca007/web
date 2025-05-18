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
    npm git golang \
    && rm -rf /var/lib/apt/lists/*

# 安装 Gotty（Web 终端）
RUN go install github.com/sorenisanerd/gotty@latest
ENV PATH="$PATH:/root/go/bin"

# 配置 Python 环境
WORKDIR /app
COPY ./app/requirements.txt .
RUN pip3 install --no-cache-dir -r requirements.txt

# 复制代码
COPY ./app /app
COPY ./app/terminal.sh /usr/bin/
COPY ./nginx.conf /etc/nginx/nginx.conf
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 暴露端口 (Render 使用环境变量 PORT)
EXPOSE 80

# 启动服务
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]