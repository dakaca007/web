FROM ubuntu:22.04

# 安装基础依赖
RUN apt update && apt install -y \
    curl \
    bash \
    procps \
    ncurses-bin \
    openssl \
    python3 \
    python3-pip \
    supervisor && \
    rm -rf /var/lib/apt/lists/*

# 安装Flask
RUN pip3 install flask

# 下载并安装GoTTY
RUN curl -LO https://github.com/yudai/gotty/releases/download/v1.0.1/gotty_linux_amd64.tar.gz && \
    tar zxvf gotty_linux_amd64.tar.gz && \
    mv gotty /usr/local/bin/ && \
    chmod +x /usr/local/bin/gotty && \
    rm gotty_linux_amd64.tar.gz

# 创建应用目录
RUN mkdir -p /home/appuser/app/static && \
    chown -R appuser:appuser /home/appuser/app

# 配置非root用户并生成证书
RUN useradd -m appuser && \
    openssl req -x509 -newkey rsa:4096 -nodes -days 365 \
      -subj "/CN=localhost" \
      -keyout /home/appuser/.gotty.key \
      -out /home/appuser/.gotty.crt && \
    chown appuser:appuser /home/appuser/.gotty.*

# 复制应用文件
COPY app.py /home/appuser/app/
COPY static/ /home/appuser/app/static/
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

USER appuser

# 安装Flask依赖（以用户身份运行）
RUN pip3 install --user flask

USER root
RUN chown -R appuser:appuser /home/appuser
USER appuser

EXPOSE 80 5000 8080

CMD ["supervisord", "-n"]