#!/bin/bash

# 定義變數
REMOTE_USER="dh_cqdvic"
REMOTE_HOST="1fbreakfast.com.tw"
REMOTE_PATH="/home/dh_cqdvic/qa.1fbreakfast.com.tw/www/public/js/opanel/"
LOCAL_PATH="public/js/opanel/"

echo "🚀 開始編譯 React 後台..."
npm run build

echo "📤 開始同步到 QA 環境 ($REMOTE_HOST)..."
rsync -avz --delete "$LOCAL_PATH" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH"

echo "✅ 同步完成！"
