FROM ubuntu:22.04

# 设置环境变量，包括时区和 Go 的 PATH
ENV DEBIAN_FRONTEND=noninteractive TZ=Asia/Shanghai \
    PATH="/usr/local/go/bin:/root/go/bin:$PATH"

# 安装必要的软件包，设置时区，并清理 apt 缓存
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone \
    && apt update && apt install -y --no-install-recommends \
       nginx supervisor wget git golang gettext \
    && rm -rf /var/lib/apt/lists/*

# 下载并安装 Go
# 修正了 Go 下载链接
RUN wget https://go.dev/dl/go1.21.1.linux-amd64.tar.gz -O go.tar.gz \
    && tar -C /usr/local -xzf go.tar.gz \
    && rm go.tar.gz

# 安装 gotty
# gotty 会被安装到 $GOPATH/bin，默认情况下对于 root 用户是 /root/go/bin
RUN go install github.com/sorenisanerd/gotty@latest

# 为 Nginx 配置创建目录
RUN mkdir -p /etc/nginx/conf.d

WORKDIR /app

# 复制终端脚本并赋予执行权限
COPY ./terminal.sh /usr/bin/terminal.sh
RUN chmod +x /usr/bin/terminal.sh

# 复制 Nginx 和 Supervisor 配置文件到正确的位置
# 将 nginx.conf 复制到 Nginx 的 sites-available/default 或 conf.d/default.conf 是标准做法
COPY ./nginx.conf /etc/nginx/conf.d/default.conf
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 暴露 Nginx 监听的端口
EXPOSE 80

# 使用 Supervisor 启动 Nginx 和 gotty
# Supervisor 的 -n 参数使其在前台运行，适合 Docker
CMD ["/usr/bin/supervisord", "-n"]