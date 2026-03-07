# 介面多語系 (UI Multi-language) 規格與維護手冊

## 1. 架構概述
本專案採用 **混合式架構 (Hybrid Architecture)**，因此多語系支援分為兩個部分：
1.  **React 前端**：負責內容區塊（如門市列表、表單）的動態渲染。
2.  **Twig 後端**：負責頁面框架（Header, Sidebar, Footer）的伺服器端渲染 (SSR)。

## 2. React 前端 i18n
使用 `i18next`、`react-i18next` 與 `i18next-browser-languagedetector` 套件。

### 檔案結構
採用 **多檔案拆分 (Multi-file)** 結構，每個模組擁有獨立的翻譯檔。

*   **設定檔**：`resources/react-opanel/src/i18n.js`
*   **翻譯檔目錄**：`resources/react-opanel/src/locales/{lang}/`
    *   `zh-TW/` (繁體中文)
    *   `zh-CN/` (簡體中文)
    *   `en/` (英文)
    *   `ko/` (韓文)
*   **檔案範例**：
    *   `common.json` (共用詞彙)
    *   `locations.json` (門市模組)
    *   `admin_users.json` (使用者模組)

### 如何新增/修改翻譯
1.  在 `resources/react-opanel/src/locales/zh-TW/` 中找到對應模組的 JSON 檔 (例如 `locations.json`)。
2.  新增或修改 JSON 鍵值對。
    ```json
    {
      "title": "門市列表",
      "add_new": "新增門市"
    }
    ```
3.  **同步更新** 其他語系目錄 (`zh-CN`, `en`, `ko`) 下的同名檔案。
4.  若新增了全新的 JSON 檔案 (例如 `new_module.json`)，必須在 `src/i18n.js` 中引入並加入 `resources` 設定。
5.  在 React Component 中使用：
    ```jsx
    import { useTranslation } from 'react-i18next';

    function MyComponent() {
      const { t } = useTranslation();
      // 使用 "檔案名.鍵名" 的格式
      return <div>{t('locations.title')}</div>;
    }
    ```

## 3. Twig 後端 i18n
使用自定義的 `I18nService` 與 Twig Extension。

### 檔案位置
*   **服務類別**：`app/Services/I18nService.php`
*   **翻譯檔目錄**：`resources/lang/{lang}/`
    *   `zh-TW/layout.php`
    *   `zh-CN/layout.php`
    *   `en/layout.php`
    *   `ko/layout.php`
*   **註冊位置**：`app/dependencies.php`

### 如何新增/修改翻譯
1.  開啟 `resources/lang/zh-TW/layout.php`。
2.  新增或修改 PHP 陣列內容。
3.  **同步更新** 其他語系目錄下的 `layout.php`。
4.  在 Twig 樣板中使用：
    ```twig
    <span>{{ t('layout.sidebar.new_item') }}</span>
    ```

## 4. 支援語系與切換機制
目前支援以下語系：
*   `zh-TW` (預設)
*   `zh-CN`
*   `en`
*   `ko`

### 切換機制
*   **Cookie**：使用名為 `i18next` 的 Cookie 儲存使用者選擇的語系。
*   **前端**：`i18next-browser-languagedetector` 自動讀取 Cookie。
*   **後端**：`dependencies.php` 初始化 `I18nService` 時讀取 Cookie。
*   **UI**：Layout Header 提供切換選單，點擊後設定 Cookie 並重新整理頁面。
