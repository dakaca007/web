FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive TZ=Asia/Shanghai \
    PATH="/usr/local/go/bin:/root/go/bin:$PATH"

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone \
    && apt update && apt install -y \
       python3 python3-pip python3-venv mysql-client \
       nginx supervisor wget unzip npm git golang gettext \
    && rm -rf /var/lib/apt/lists/* \
    && wget https://go.dev/dl/go1.21.1.linux-amd64.tar.gz \
    && tar -C /usr/local -xzf go1.21.1.linux-amd64.tar.gz \
    && rm go1.21.1.linux-amd64.tar.gz \
    && mkdir -p /etc/nginx/templates \
    && go install github.com/sorenisanerd/gotty@latest

WORKDIR /app
COPY ./terminal.sh /usr/bin/terminal.sh
RUN chmod +x /usr/bin/terminal.sh

COPY ./nginx.conf /etc/nginx/templates/nginx.conf
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80 3000

CMD envsubst '$PORT' \
     < /etc/nginx/templates/nginx.conf \
     > /etc/nginx/nginx.conf \
  && supervisord -n
