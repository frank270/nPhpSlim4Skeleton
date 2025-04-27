#!/bin/bash

# 建立運行時必要的目錄
mkdir -p cache/twig
mkdir -p logs
mkdir -p docker/ssl

# 設定正確的目錄權限
chmod -R 777 cache
chmod -R 777 logs

# 複製環境變數配置
if [ -f .env.example ]; then
  cp -n .env.example .env
  echo "✅ 已複製 .env.example 到 .env，請根據環境修改配置"
else
  echo "⚠️ 未找到 .env.example 文件，請手動建立 .env 設定環境變數"
fi

# 安裝依賴
echo "正在安裝composer依賴..."
composer install

# 顯示結果
echo ""
echo "✅ 初始化完成！"
echo ""
echo "✅ 請記得："
echo "- 確認 .env 檔案中的資料庫和Redis連線設定"
echo "- 使用以下命令生成本地SSL憑證："
echo "  mkdir -p ./docker/ssl"
echo "  mkcert -install  # 如果尚未安裝本地CA"
echo "  mkcert -key-file ./docker/ssl/ndev.local.key -cert-file ./docker/ssl/ndev.local.crt ndev.local \"*.ndev.local\""
echo "  mkcert -key-file ./docker/ssl/ndba.local.key -cert-file ./docker/ssl/ndba.local.crt ndba.local \"*.ndba.local\""
echo "- 確保hosts檔案中已添加："
echo "  127.0.0.1 ndev.local"
echo "  127.0.0.1 ndba.local"
echo ""
echo "啟動Docker環境："
echo "docker compose up -d"
