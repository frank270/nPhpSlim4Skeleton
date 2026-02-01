#!/bin/bash

# 定義變數
REMOTE_USER="dh_cqdvic"
REMOTE_HOST="1fbreakfast.com.tw"
# 專案根目錄 (根據舊腳本路徑推斷)
REMOTE_ROOT="/home/dh_cqdvic/qa.1fbreakfast.com.tw/www/"

# 確保在專案根目錄執行 (簡單檢查)
if [ ! -d "app" ]; then
    echo "❌ 錯誤：請在專案根目錄 (www) 下執行此腳本"
    exit 1
fi

echo "🚀 步驟 1/2: 編譯 React 前端資源..."
# 進入 React 目錄編譯
cd resources/react-opanel
# 檢查是否有安裝依賴，若無則安裝 (加速流程)
if [ ! -d "node_modules" ]; then
    npm install
fi
npm run build
# 回到根目錄
cd ../..

echo "📤 步驟 2/2: 同步檔案到 QA 環境 ($REMOTE_HOST)..."
echo "   目標路徑: $REMOTE_ROOT"

# 同步核心與資產，但排除設定檔與使用者上傳檔案
rsync -avz --delete \
    --exclude '.env' \
    --exclude '.env.*' \
    --exclude '.git/' \
    --exclude '.gitignore' \
    --exclude 'node_modules/' \
    --exclude 'resources/react-opanel/node_modules/' \
    --exclude 'docker/' \
    --exclude 'tests/' \
    --exclude 'cache/' \
    --exclude 'logs/' \
    --exclude 'public/upload/' \
    --exclude '.DS_Store' \
    --exclude 'bin/' \
    ./ "$REMOTE_USER@$REMOTE_HOST:$REMOTE_ROOT"

echo "✅ QA 部署完成！"
echo "   請記得在遠端執行資料庫遷移 (如需):"
echo "   ssh $REMOTE_USER@$REMOTE_HOST 'cd $REMOTE_ROOT && php-8.3 vendor/bin/doctrine-migrations migrate --no-all-or-nothing'"
