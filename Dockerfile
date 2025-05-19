# 使用Ubuntu基础镜像确保glibc环境
FROM ubuntu:22.04

# 安装基础依赖
RUN apt update && apt install -y curl

# 下载并安装GoTTY（无需解压嵌套目录）
RUN curl -LO https://github.com/yudai/gotty/releases/download/v2.0.0-alpha.3/gotty_2.0.0-alpha.3_linux_amd64.tar.gz && \
    tar zxvf gotty_2.0.0-alpha.3_linux_amd64.tar.gz && \
    mv gotty /usr/local/bin/ && \
    chmod +x /usr/local/bin/gotty && \
    rm gotty_2.0.0-alpha.3_linux_amd64.tar.gz
   

USER root
EXPOSE 80
# 使用Render动态端口
CMD ["gotty", "--port", "80", "--credential", "$GOTTY_CREDENTIAL", "bash"]