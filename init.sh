#!/bin/bash

# 建立主要資料夾
mkdir -p app/src
mkdir -p app/templates
mkdir -p cache/twig
mkdir -p log
#mkdir -p public

# 建立空白檔案
touch app/settings.php
touch app/database.php
touch app/dependencies.php
touch app/middleware.php
touch app/routes.php
#touch public/index.php
#touch public/.htaccess

# 顯示結果
echo "資料夾與檔案已建立完成！結構如下："
tree -I 'vendor|node_modules'  # 如果有 tree 指令會列出結構

# 顯示小提醒
echo ""
echo "✅ 請記得："
echo "- public/index.php 是入口點"
echo "- app/settings.php 等待填入設定"
echo "- public/.htaccess 需要加上 Rewrite 規則"
