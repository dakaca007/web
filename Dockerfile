# 使用Alpine镜像并启用root账户
FROM alpine:latest

# 安装依赖并配置root权限
RUN apk add --no-cache bash curl shadow && \
    curl -L https://github.com/yudai/gotty/releases/download/v2.0.0-alpha.3/gotty_2.0.0-alpha.3_linux_amd64.tar.gz -o gotty.tar.gz && \
    tar zxvf gotty.tar.gz && \
     # 移动文件并赋予执行权限
    mv gotty_2.0.0-alpha.3_linux_amd64/gotty /usr/local/bin/ && \
    chmod +x /usr/local/bin/gotty && \
    rm gotty.tar.gz && \
    # 允许root通过终端登录
    sed -i 's/^root:!:/root::/' /etc/shadow

# 切换到root用户
USER root
EXPOSE 80
# 设置启动命令（绑定低端口需root权限）
CMD ["gotty", "--port", "80", "--credential", "$GOTTY_CREDENTIAL", "--permit-arguments", "bash"]