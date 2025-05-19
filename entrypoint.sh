#!/usr/bin/env bash
set -e

# 1. 以 root 身份启动 nginx
echo "Starting nginx..."
service nginx start

# 2. 以 appuser 身份启动 Gotty
echo "Starting Gotty as appuser..."
exec gotty "$@"
