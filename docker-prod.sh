#!/bin/bash

# 確保腳本可以在任何目錄執行
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
cd "$SCRIPT_DIR"

# 使用生產環境的配置文件
echo "使用生產環境配置 (.env.docker.prod)..."
docker compose --env-file ./.env.docker.prod up -d

echo "生產環境容器已啟動！"