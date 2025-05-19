FROM ubuntu:22.04

# 安装基础依赖 & Nginx
RUN apt update && apt install -y \
    curl \
    bash \
    procps \
    ncurses-bin \
    openssl \
    nginx \           
    && rm -rf /var/lib/apt/lists/*

# 下载并安装稳定版 GoTTY
RUN curl -LO https://github.com/yudai/gotty/releases/download/v1.0.1/gotty_linux_amd64.tar.gz \
 && tar zxvf gotty_linux_amd64.tar.gz \
 && mv gotty /usr/local/bin/ \
 && chmod +x /usr/local/bin/gotty \
 && rm gotty_linux_amd64.tar.gz

# 配置非 root 用户并生成 TLS 证书
RUN useradd -m appuser \
 && openssl req -x509 -newkey rsa:4096 -nodes -days 365 \
      -subj "/CN=localhost" \
      -keyout /home/appuser/.gotty.key \
      -out /home/appuser/.gotty.crt \
 && chown appuser:appuser /home/appuser/.gotty.*

# 复制 Nginx 配置模板
COPY nginx.conf /etc/nginx/sites-available/default

# 添加入口脚本，用于启动 nginx + gotty
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# 切换到非 root 用户
USER appuser

# 暴露唯一的 80 端口
EXPOSE 80

# 启动脚本
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["--permit-write", "--port", "80", "bash"]
