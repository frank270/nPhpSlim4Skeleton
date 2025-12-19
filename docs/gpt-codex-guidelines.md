# GPT Codex 開發遵循原則

## 專案結構與命名
1. 遵循既有資料夾與檔案拆分方式，例如每個後台功能使用獨立的 `app/Routes/opanel_*.php` 以及 `App\Actions\Opanel\*Action`。
2. 不得將多個大功能攏統放入同一檔案；新增功能必須依現有結構建立專屬 Route 與 Action。
3. 保持命名規則，例如 URL 路徑使用 `/module/resource/...`，動作以 `create/edit/delete/toggle-status/update-order` 等既有字尾。
4. **側邊欄選單**：新增後台模組時，**務必**於 `app/Templates/opanel/layout.twig` 增加對應的連結項目，切勿遺漏。

## 後台技術棧
1. 後台畫面採用 React（位於 `resources/react-opanel/`），非 Twig；新增 UI 功能需在 React 程式碼中實作。
2. **編譯要求 (Critical)**：任何對 `resources/react-opanel/` 目錄下的修改，**必須**隨後執行 `npm run build` (於該目錄下)，否則變更不會生效。禁止將修改 React 視為修改 interpreted language。
3. **UI 一致性**：新增 UI 元件 (如 Input, Select, Tags) 前，**必須**先檢查既有模組 (e.g., Food Safety) 的實作方式，確保風格與操作體驗一致，嚴禁擅自創造非標準的介面。
4. PHP 端提供資料（JSON API）與權限/日誌處理。

## 邏輯確認原則 (Logic Verification)
1. **需求確認**：在進行任何「複雜邏輯修改」或「排版結構變更」前，**必須**先向使用者複述理解並要求確認 (e.g., "請確認你理解")，**嚴禁**在未獲批准前直接寫 Code。
2. **參數傳遞驗證**：新增篩選、排序等功能時，必須追蹤資料流 (Controller -> Model)，確認參數 (如 `keyword`, `tag`) 確實被傳遞並用於 SQL 查詢，不可假設參數會自動生效。

## 資料存取層
1. 透過 Doctrine DBAL `Connection` 搭配 `app/Models/` 下的 Model 類別處理資料庫操作。
2. 新增 Model 時需遵循既有寫法（建構子注入 `Connection`、使用 QueryBuilder、提供 CRUD 與必要業務方法）。

## API 設計
1. API 路由需使用 `/opanel/...` 前綴並遵循既有 CRUD 命名，例如：
   - `GET /.../list`
   - `POST /.../create`
   - `POST /.../{id}/edit`
   - `DELETE /.../{id}/delete`
   - `POST /.../{id}/toggle-status`
2. Action 方法回傳 JSON，使用 `respondJson`，並在必要時記錄操作日誌與檢查權限。
3. **請求格式規範**：
   - 後台 React 前端發送 POST/PUT 請求時，**必須**使用 `application/x-www-form-urlencoded` 格式 (使用 `URLSearchParams`)。
   - **嚴禁**使用 `JSON.stringify` 發送資料，因為後端 Slim 框架預設未全域啟用 JSON Body 解析器。
   - 範例：
     ```javascript
     const formData = new URLSearchParams();
     formData.append('key', value);
     await fetch(url, {
         method: 'POST',
         headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
         body: formData.toString()
     });
     ```

## 變更流程
1. 編輯前需先提供預計修改內容與原因，取得確認後再動手。
2. 撰寫遷移檔後，需使用容器內的 Doctrine Migrations 指令驗證 (`docker compose exec app php vendor/bin/doctrine-migrations migrate ...`)。
3. 每次變更完成後更新 Git 分支並提交；Commit 訊息應簡潔且對應實際調整內容。

## 其他注意事項
1. 嚴禁擅自修改配置或破壞現有結構，除非經過明確指示。
2. 文件、程式與前端邏輯需保持同步，如新增功能必須同步更新對應的 docs 與 React 程式。
3. 保持代碼風格與既有檔案一致（縮排、命名、註解風格等）。

## 多語系 (i18n) 開發規範
1. **禁止 Hardcode**：所有 UI 顯示文字（標題、按鈕、提示訊息、錯誤訊息等）嚴禁直接寫死在程式碼中，必須使用翻譯鍵值。
   - React: `t('module.key')`
   - Twig: `{{ t('module.key') }}`
2. **多語系支援**：新功能必須同時支援以下 4 種語系：
   - 繁體中文 (`zh-TW`) - **主要開發語系**
   - 簡體中文 (`zh-CN`)
   - 英文 (`en`)
   - 韓文 (`ko`)
3. **檔案維護**：
   - **React 前端**：於 `resources/react-opanel/src/locales/{lang}/{module}.json` 新增對應翻譯檔。
   - **Twig 後端**：於 `resources/lang/{lang}/layout.php` (或其他對應檔案) 新增翻譯。
4. **鍵值命名**：使用小寫蛇形命名 (snake_case)，層級應清晰，例如 `locations.add_new` 或 `common.error`。
5. **字串長度與簡寫**：生成語系檔時，需考慮該語系的字串長度是否會過長導致破版。若過長，應使用該國家通用的簡略語 (Abbreviations)。
