FROM ubuntu:22.04

# 安装基础依赖
RUN apt update && apt install -y \
    curl \
    bash \
    procps \
    ncurses-bin

# 下载并安装稳定版GoTTY
RUN curl -LO https://github.com/yudai/gotty/releases/download/v1.0.1/gotty_linux_amd64.tar.gz && \
    tar zxvf gotty_linux_amd64.tar.gz && \
    mv gotty /usr/local/bin/ && \
    chmod +x /usr/local/bin/gotty && \
    rm gotty_linux_amd64.tar.gz

# 配置非root用户
RUN useradd -m appuser && chown -R appuser /home/appuser
USER appuser

EXPOSE 80
CMD ["gotty", "-t", "--permit-write", "--port", "80", "bash"]