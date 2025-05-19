FROM ubuntu:22.04

# 安装基础依赖
RUN apt update && apt install -y \
    curl \
    bash \
    procps \
    ncurses-bin \
    openssl  # 添加 openssl 用于生成证书

# 下载并安装稳定版GoTTY
RUN curl -LO https://github.com/yudai/gotty/releases/download/v1.0.1/gotty_linux_amd64.tar.gz && \
    tar zxvf gotty_linux_amd64.tar.gz && \
    mv gotty /usr/local/bin/ && \
    chmod +x /usr/local/bin/gotty && \
    rm gotty_linux_amd64.tar.gz

# 配置非root用户并生成证书
RUN useradd -m appuser && \
    openssl req -x509 -newkey rsa:4096 -nodes -days 365 \
      -subj "/CN=localhost" \
      -keyout /home/appuser/.gotty.key \
      -out /home/appuser/.gotty.crt && \
    chown appuser:appuser /home/appuser/.gotty.*

USER appuser

EXPOSE 80
CMD ["gotty", "--permit-write", "--port", "80", "bash"]