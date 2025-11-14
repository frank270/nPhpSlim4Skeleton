# External Links 功能開發回顧（錯誤與疏漏）

> 建立時間：2025-11-14 16:56 (UTC+08:00)

## 發生問題與未注意的細節

1. **路由與模板路徑誤用**  
   初始仍參照舊的 `opanel/settings` 路徑，導致 Twig 模板載入錯誤並回傳 500。
2. **左側選單遺漏**  
   未在 `layout.twig` 加入「外部連結管理」項目，使頁面缺乏入口。
3. **React 頁面初次顯示為空白**  
   忽略重新 build `externalLinks.bundle.js`，造成 React 容器無法掛載內容。
4. **API 未處理 JSON 請求**  
   仍使用 `getParsedBody()` 解析資料，導致前端送出的 JSON 錯誤回傳「缺少 uuid」。
5. **狀態切換日誌記錄錯誤**  
   誤用不存在的 `LogUtil::adminLog()`，造成停用／啟用 API 回傳 500。

## 改進作法與後續提醒

- 建立功能開發 Checklist，涵蓋路由、模板路徑、選單與 API Base Path 的同步調整。
- 前端改動後務必執行 `nvm use && npm run build`，並確認 bundle 檔已更新。
- 後端統一使用 `parseRequestData()` 處理 JSON 與 form-data，避免解析落差。
- 操作記錄一律透過 `logAction()`，避免依賴未存在的工具函式。
- 每次推送前檢查 docs 是否需要同步更新，確保進度文件與功能狀態一致。
