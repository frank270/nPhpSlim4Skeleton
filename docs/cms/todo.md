# CMS 內容區塊模組待辦清單

## 後端
- [ ] 設計並建立 `cms_content_blocks`（與可選 `cms_sections`）資料表遷移。
- [ ] 實作 `ContentBlocksModel`（查詢、依 route/section/locale 篩選、排序、軟刪除）。
- [ ] 新增 `ContentBlockAction` + 對應路由（列表、建立、更新、切換狀態、刪除、排序）。
- [ ] 實作 route/section cache helper 與清除機制（更新後失效該 route 快取）。
- [ ] 透過 `logAction()` 記錄所有 CRUD 操作，補上 PermissionMiddleware 設定。

## 前端（React 後台）
- [ ] 建立 `/opanel/content-blocks` React 頁面：篩選、列表、排序拖曳。
- [ ] 整合 Quill（含客製 toolbar、圖片上傳 handler、媒體選擇器按鈕）。
- [ ] 圖片區塊編輯表單：媒體資源庫 modal、alt/caption/link 輸入、預覽。
- [ ] 多語/section/route 選單與快速複製現有 block。
- [ ] UX：儲存/部署提示、草稿/發布切換、進度條/錯誤訊息。

## 前台與整合
- [ ] 建立 `ContentBlocksModel::findActiveByRoute`（或 service function）並注入 Twig 變數。
- [ ] 調整首頁/關於/菜單等 controller，改用內容區塊資料替代舊 home content。
- [ ] 若需 API：新增 `GET /api/front/content-blocks`，限制內容只讀。
- [ ] 撰寫資料快取策略與失效流程，並更新硬體監控/日誌需求。

## 內容與測試
- [ ] 整理 route + section 對照表（包含顯示位置、允許型態、範例）。
- [ ] Quill HTML 測試（XSS/LF、空白段落），確認儲存/渲染一致。
- [ ] 圖片上傳與媒體複用流程、拖曳排序、快取清除驗證。
- [ ] 文件更新：需求、Sprint checklist、守則與操作教學。
