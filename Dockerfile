FROM ubuntu:22.04

# 设置环境变量
ENV DEBIAN_FRONTEND=noninteractive TZ=Asia/Shanghai \
    PATH="/usr/local/go/bin:/root/go/bin:$PATH"

# 安装依赖、Go、gotty、配置文件等
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone \
    && apt update \
    && apt install -y --no-install-recommends \
       nginx supervisor wget git gettext ca-certificates \
    && rm -rf /var/lib/apt/lists/* \
    && wget https://go.dev/dl/go1.21.1.linux-amd64.tar.gz -O - | tar -C /usr/local -xzf - \
    && mkdir -p /root/go /etc/nginx/conf.d /run/nginx /var/log/supervisor

# 安装 gotty
RUN go install github.com/sorenisanerd/gotty@latest

# 复制 terminal.sh 并赋予执行权限
COPY ./terminal.sh /usr/bin/terminal.sh
RUN chmod +x /usr/bin/terminal.sh

# 复制配置文件
COPY ./nginx.conf /etc/nginx/conf.d/default.conf
COPY ./supervisord.conf /etc/supervisor/supervisord.conf

# 暴露端口
EXPOSE 80

# 启动 Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf", "-n"]