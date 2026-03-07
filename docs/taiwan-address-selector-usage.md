# 台灣地址選擇器使用說明

## 檔案位置

- **JS 檔案**：`public/js/taiwan-address-selector.js`
- **JSON 資料**：`public/js/data/taiwan-zipcode-data.json`

## 基本使用

### HTML

```html
<!-- 引入 JS 檔案 -->
<script src="/js/taiwan-address-selector.js"></script>

<!-- 建立容器 -->
<div id="address-container"></div>

<script>
  // 初始化
  const selector = new TaiwanAddressSelector({
    containerId: 'address-container'
  });
</script>
```

## 完整範例（含自訂樣式）

```html
<!DOCTYPE html>
<html>
<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h2>地址選擇器範例</h2>
    
    <!-- 容器 -->
    <div id="my-address-selector"></div>
    
    <!-- 顯示選取值 -->
    <div class="mt-3">
      <p>選取值：<span id="selected-values"></span></p>
    </div>
  </div>

  <script src="/js/taiwan-address-selector.js"></script>
  <script>
    const selector = new TaiwanAddressSelector({
      containerId: 'my-address-selector',
      
      // 自訂 CSS class（使用 Bootstrap）
      classes: {
        county: 'form-control form-control-lg',
        district: 'form-control form-control-lg',
        zipcode: 'form-control form-control-lg'
      },
      
      // 自訂標籤
      labels: {
        county: '縣市',
        district: '行政區',
        zipcode: '郵遞區號'
      },
      
      // 自訂 placeholder
      placeholders: {
        county: '請選擇或輸入縣市',
        district: '請選擇或輸入行政區',
        zipcode: '請選擇或輸入郵遞區號'
      },
      
      // 預設值（可選）
      values: {
        county: '新北市',
        district: '中和區',
        zipcode: '235'
      },
      
      // 變更回呼
      onChange: (county, district, zipcode) => {
        document.getElementById('selected-values').textContent = 
          `${county} ${district} ${zipcode}`;
        console.log('選取值:', { county, district, zipcode });
      }
    });
  </script>
</body>
</html>
```

## API 說明

### 建構函數選項

| 參數 | 類型 | 必填 | 說明 |
|------|------|------|------|
| `containerId` | string | 是 | 容器元素的 ID |
| `jsonUrl` | string | 否 | JSON 資料路徑（預設：`/js/data/taiwan-zipcode-data.json`） |
| `classes` | object | 否 | 自訂 CSS class |
| `classes.county` | string | 否 | 城市欄位的 class |
| `classes.district` | string | 否 | 行政區欄位的 class |
| `classes.zipcode` | string | 否 | 郵遞區號欄位的 class |
| `labels` | object | 否 | 自訂標籤文字 |
| `placeholders` | object | 否 | 自訂 placeholder |
| `values` | object | 否 | 預設值 |
| `onChange` | function | 否 | 變更回呼函數 |

### 公開方法

#### `setCounty(county)`
設定城市並更新行政區選單

```javascript
selector.setCounty('新北市');
```

#### `setDistrict(district)`
設定行政區並更新郵遞區號

```javascript
selector.setDistrict('中和區');
```

#### `setZipcode(zipcode)`
設定郵遞區號

```javascript
selector.setZipcode('235');
```

#### `getValues()`
取得目前選取的值

```javascript
const values = selector.getValues();
console.log(values);
// { county: '新北市', district: '中和區', zipcode: '235' }
```

#### `reset()`
重置所有欄位

```javascript
selector.reset();
```

## 功能特點

1. **三層聯動**：選擇城市後自動更新行政區選單，選擇行政區後自動填入郵遞區號
2. **原生 HTML datalist**：使用原生 HTML5 datalist，無需額外依賴
3. **可自訂樣式**：支援自訂 CSS class
4. **可自訂文字**：支援自訂標籤和 placeholder
5. **事件回呼**：支援 onChange 回呼函數
6. **程式化控制**：提供公開方法可程式化設定值

## 注意事項

- 容器元素必須存在於 DOM 中
- JSON 檔案必須可透過 HTTP 存取
- 使用 `datalist` 時，使用者可以輸入或選擇
- 行政區和郵遞區號欄位在未選擇城市前會是 disabled 狀態

