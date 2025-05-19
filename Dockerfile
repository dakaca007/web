FROM alpine:latest

# 安装基础依赖（移除shadow包）
RUN apk add --no-cache bash curl

# 下载GoTTY并处理路径
RUN curl -LO https://github.com/yudai/gotty/releases/download/v2.0.0-alpha.3/gotty_2.0.0-alpha.3_linux_amd64.tar.gz && \
    tar zxvf gotty_2.0.0-alpha.3_linux_amd64.tar.gz && \
    mv gotty_2.0.0-alpha.3_linux_amd64/gotty /usr/local/bin/ && \
    chmod +x /usr/local/bin/gotty && \
    rm -rf gotty_2.0.0-alpha.3_linux_amd64*

# 允许root登录（无需shadow包）
RUN sed -i 's/^root:!:/root::/' /etc/shadow

USER root
EXPOSE 80
# 设置启动命令（绑定低端口需root权限）
CMD ["gotty", "--port", "80", "--credential", "$GOTTY_CREDENTIAL", "--permit-arguments", "bash"]