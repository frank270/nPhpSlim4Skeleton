# 菜單批次上傳與 ZIP 圖檔處理架構分析

此文件原為討論同步/非同步的完整上傳機制。
根據使用者後續確認：
1. **圖檔與 CSV 分拆為兩階段上傳**
2. **新增「菜單編號」(item_code)** 作為關聯鍵
3. **介面需提供 CSV 範本下載** 以降低管理者操作失誤率

由於架構已大幅簡化，不須走複雜的兩階段預覽或背景佇列（Queue），預計將實作兩支輕量化與穩定的同步處理 API：
- `POST /opanel/menu/batch/csv`: 處理文字資料
- `POST /opanel/menu/batch/zip`: 依據檔名對應 `item_code` 自動入圖

詳細的 API 規格與實作步驟，請見 `implementation_plan.md`。
