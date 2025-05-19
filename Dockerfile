FROM alpine:latest

# 安装基础依赖（移除shadow包）
RUN apk add --no-cache bash curl

# 添加分步调试输出
RUN echo "=== 开始下载GoTTY ===" && \
    curl -LO https://github.com/yudai/gotty/releases/download/v2.0.0-alpha.3/gotty_2.0.0-alpha.3_linux_amd64.tar.gz && \
    echo "=== 下载完成 ===" && ls -lh *.tar.gz && \
    \
    echo "=== 创建临时目录 ===" && \
    mkdir -p /tmp/gotty && ls -ld /tmp/gotty && \
    \
    echo "=== 解压文件（显示详细列表）===" && \
    tar zxvf gotty_2.0.0-alpha.3_linux_amd64.tar.gz -C /tmp/gotty --strip-components=1 -v && \
    \
    echo "=== 查看解压结果 ===" && \
    echo "临时目录内容：" && ls -lR /tmp/gotty && \
    \
    echo "=== 移动可执行文件 ===" && \
    mv -v /tmp/gotty/gotty_2.0.0-alpha.3_linux_amd64/gotty /usr/local/bin/ && \
    \
    echo "=== 设置执行权限 ===" && \
    chmod -v +x /usr/local/bin/gotty && \
    \
    echo "=== 清理残留文件 ===" && \
    rm -rfv /tmp/gotty gotty_2.0.0-alpha.3_linux_amd64.tar.gz

# 显示最终文件状态
RUN echo "=== 验证安装结果 ===" && \
    ls -l /usr/local/bin/gotty && \
    ldd /usr/local/bin/gotty || echo "非动态链接文件"

# 允许root登录（无需shadow包）
RUN sed -i 's/^root:!:/root::/' /etc/shadow

USER root
EXPOSE 80
# 设置启动命令（绑定低端口需root权限）
CMD ["gotty", "--port", "80", "--credential", "$GOTTY_CREDENTIAL", "--permit-arguments", "bash"]