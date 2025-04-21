#!/bin/bash

# 建立 SSL 憑證目錄
mkdir -p /etc/nginx/ssl

# 生成 ndev.local 的 SSL 憑證
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/nginx/ssl/ndev.local.key \
    -out /etc/nginx/ssl/ndev.local.crt \
    -subj "/C=TW/ST=Taiwan/L=Taipei/O=Development/CN=ndev.local"

# 生成 ndba.local 的 SSL 憑證
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/nginx/ssl/ndba.local.key \
    -out /etc/nginx/ssl/ndba.local.crt \
    -subj "/C=TW/ST=Taiwan/L=Taipei/O=Development/CN=ndba.local"

# 設定權限
chmod 644 /etc/nginx/ssl/*.crt
chmod 600 /etc/nginx/ssl/*.key

echo "SSL 憑證已生成完成。"